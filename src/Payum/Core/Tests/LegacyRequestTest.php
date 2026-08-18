<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use League\Uri\Uri;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CancelHandlerInterface;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\RequestToCommand;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Result\CancelResult;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * A gateway package moves to handlers on its own schedule. An application calling execute(new Capture())
 * should not break because of it.
 */
final class LegacyRequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        LegacyCaptureHandler::$next = null;
        LegacyCaptureHandler::$dispatched = false;
    }

    public function testShouldAnswerAOneXCaptureWithTheHandler(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $gateway->execute(new Capture($this->buildPayment()));

        $this->assertTrue(LegacyCaptureHandler::$dispatched);
    }

    public function testShouldThrowTheReplyMatchingWhatTheHandlerDecided(): void
    {
        LegacyCaptureHandler::$next = new Redirect('https://acme.test/checkout/1');

        $gateway = $this->buildPayum()->getGateway('acme');

        try {
            $gateway->execute(new Capture($this->buildPayment()));

            $this->fail('Expected a reply to be thrown.');
        } catch (HttpRedirect $reply) {
            $this->assertSame('https://acme.test/checkout/1', $reply->getUrl());
        }
    }

    public function testShouldReturnTheReplyWhenTheCallerAsksToCatchIt(): void
    {
        LegacyCaptureHandler::$next = new Redirect('https://acme.test/checkout/1');

        $gateway = $this->buildPayum()->getGateway('acme');

        $reply = $gateway->execute(new Capture($this->buildPayment()), true);

        $this->assertInstanceOf(HttpRedirect::class, $reply);
    }

    public function testShouldTranslateEachRequestToItsOwnCommand(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $gateway->execute(new Cancel($this->buildPayment()));

        $this->assertTrue(LegacyCancelHandler::$dispatched);
    }

    public function testShouldReportNotifyAsUnsupportedWhenTheGatewayShipsNoHandlerForIt(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        // The request translates to NotifyCommand fine; this gateway just ships no handler for it.
        $this->expectException(RequestNotSupportedException::class);

        $gateway->execute(new Notify($this->buildPayment()));
    }

    public function testShouldTranslateANotifyRequestCarryingAToken(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $command = RequestToCommand::translate(new Notify($token));

        $this->assertInstanceOf(NotifyCommand::class, $command);
        $this->assertSame($token, $command->token());
    }

    public function testShouldTranslateANotifyRequestCarryingAPayment(): void
    {
        $payment = new Payment();

        $command = RequestToCommand::translate(new Notify($payment));

        $this->assertInstanceOf(NotifyCommand::class, $command);
        $this->assertSame($payment, $command->subject());
    }

    public function testShouldNotTranslateANotifyRequestCarryingOnlyDetails(): void
    {
        // The surviving 1.x NotifyActions match on an ArrayAccess model. Leave those requests to them.
        $this->assertNull(RequestToCommand::translate(new Notify(new ArrayObject())));
    }

    public function testShouldAnswerAOneXStatusRequestFromTheRecordedStatus(): void
    {
        $payment = new LegacyTrackedPayment();
        $payment->setStatus(PaymentStatus::Captured);

        $gateway = $this->buildPayum()->getGateway('acme');

        $gateway->execute($status = new GetHumanStatus($payment));

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldSayUnknownForASubjectThatTracksNoStatus(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $gateway->execute($status = new GetHumanStatus($this->buildPayment()));

        // Nobody knows, which is not the same as new.
        $this->assertTrue($status->isUnknown());
    }

    public function testDoneShouldWorkAgainstAGatewayBuiltFromHandlers(): void
    {
        $payum = $this->buildPayum();

        /** @var LegacyTrackedPayment $payment */
        $payment = $payum->getStorage(LegacyTrackedPayment::class)->create();
        $payment->setNumber(uniqid());
        $payment->setStatus(PaymentStatus::Captured);
        $payum->getStorage(LegacyTrackedPayment::class)->update($payment);

        $token = $payum->getTokenFactory()->createCaptureToken('acme', $payment, 'done.php');

        // The plain-php verifier checks the current url against the token's target, from globals.
        $_SERVER['REQUEST_URI'] = $token->getTargetUrl();

        $done = $payum->done([
            'payum_token' => $token->getHash(),
        ]);

        // Broken before this: done() dispatches GetHumanStatus, which a handler gateway had no action for.
        $this->assertInstanceOf(LegacyTrackedPayment::class, $done);
        $this->assertSame($payment->getNumber(), $done->getNumber());
    }

    private function buildPayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('EUR');

        return $payment;
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(): Payum
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            ->addStorage(LegacyTrackedPayment::class, new FilesystemStorage(
                sys_get_temp_dir(),
                LegacyTrackedPayment::class,
                'number',
            ))
            ->registerGateway('acme', new LegacyConfig())
            ->getPayum();
    }
}

final class LegacyConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return LegacyGateway::class;
    }
}

final class LegacyGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return LegacyConfig::class;
    }

    public function handlers(): array
    {
        return [LegacyCaptureHandler::class, LegacyCancelHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class LegacyCaptureHandler implements CaptureHandlerInterface
{
    public static ?Redirect $next = null;

    public static bool $dispatched = false;

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        self::$dispatched = true;

        return self::$next instanceof Redirect
            ? CaptureResult::pending(self::$next)
            : CaptureResult::captured('txn_1');
    }
}

final class LegacyCancelHandler implements CancelHandlerInterface
{
    public static bool $dispatched = false;

    public function handle(CancelCommand $command, Context $context): CancelResult
    {
        self::$dispatched = true;

        return CancelResult::canceled();
    }
}

class LegacyTrackedPayment extends Payment implements StatusAwareInterface
{
    private PaymentStatus $status = PaymentStatus::New;

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): void
    {
        $this->status = $status;
    }
}
