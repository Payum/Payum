<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use League\Uri\Uri;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Gateway\DeclaresActions;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Metadata\Logo;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Refund;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * A gateway does not have to move all at once. What it has ported goes to a handler; what it has not goes
 * to the actions it still has.
 */
final class HalfMigratedGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        HalfCaptureHandler::$ran = false;
        HalfCaptureAction::$ran = false;
        HalfRefundAction::$ran = false;
        HalfStatusAction::$ran = false;
    }

    public function testShouldSendAPortedRequestToItsHandlerRatherThanTheActionItReplaced(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $gateway->execute(new Capture($this->buildPayment()));

        // The gateway still carries the capture action it has not deleted yet. The handler is what
        // answers, or porting an operation would change nothing.
        $this->assertTrue(HalfCaptureHandler::$ran);
        $this->assertFalse(HalfCaptureAction::$ran);
    }

    public function testShouldLeaveAnUnportedRequestToTheActionThatStillHandlesIt(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $gateway->execute(new Refund($this->buildPayment()));

        $this->assertTrue(HalfRefundAction::$ran);
        $this->assertFalse(HalfCaptureHandler::$ran);
    }

    public function testShouldStillTakeCommandsDirectly(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $result = $gateway->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertInstanceOf(CaptureResult::class, $result);
        $this->assertTrue(HalfCaptureHandler::$ran);
    }

    public function testShouldPreferAStatusActionOverTheRecordedStatus(): void
    {
        $payment = new HalfTrackedPayment();
        // What Payum recorded says pending; the gateway's own action knows better, because it reads the
        // details of the part that has not moved.
        $payment->setStatus(PaymentStatus::Pending);

        $gateway = $this->buildPayum()->getGateway('acme');

        $gateway->execute($status = new GetHumanStatus($payment));

        $this->assertTrue(HalfStatusAction::$ran);
        $this->assertTrue($status->isCaptured());
    }

    public function testShouldGiveAGatewayThatDeclaresNoActionsACleanGateway(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('pure', new PureConfig())
            ->getPayum();

        // Nothing legacy on it at all, so a request with no handler behind it is simply unsupported.
        $this->expectException(RequestNotSupportedException::class);

        $payum->getGateway('pure')->execute(new Refund($this->buildPayment()));
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
            ->registerGateway('acme', new HalfConfig())
            ->getPayum();
    }
}

final class HalfConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return HalfGateway::class;
    }
}

final class HalfGateway implements PaymentGateway, DeclaresActions
{
    public function configClass(): string
    {
        return HalfConfig::class;
    }

    public function handlers(): array
    {
        return [HalfCaptureHandler::class];
    }

    public function actions(): array
    {
        return [HalfCaptureAction::class, HalfRefundAction::class, HalfStatusAction::class];
    }

    public function logo(): Logo
    {
        return Logo\Url::create('https://acme.test/logo.svg');
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

final class PureConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return PureGateway::class;
    }
}

final class PureGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return PureConfig::class;
    }

    public function handlers(): array
    {
        return [HalfCaptureHandler::class];
    }

    public function logo(): Logo
    {
        return Logo\Url::create('https://acme.test/logo.svg');
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

final class HalfCaptureHandler implements CaptureHandlerInterface
{
    public static bool $ran = false;

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        self::$ran = true;

        return CaptureResult::captured('txn_1');
    }
}

final class HalfCaptureAction implements ActionInterface
{
    public static bool $ran = false;

    public function execute($request): void
    {
        self::$ran = true;
    }

    public function supports($request): bool
    {
        return $request instanceof Capture;
    }
}

final class HalfRefundAction implements ActionInterface
{
    public static bool $ran = false;

    public function execute($request): void
    {
        self::$ran = true;
    }

    public function supports($request): bool
    {
        return $request instanceof Refund;
    }
}

final class HalfStatusAction implements ActionInterface
{
    public static bool $ran = false;

    public function execute($request): void
    {
        self::$ran = true;

        /** @var GetStatusInterface $request */
        $request->markCaptured();
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface;
    }
}

class HalfTrackedPayment extends Payment implements StatusAwareInterface
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
