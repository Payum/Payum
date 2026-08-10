<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\CommandNotSupportedException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Payment;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end: a gateway registered by config, a command dispatched to its handler, and the re-entrancy
 * that the whole capture flow rests on.
 */
final class CommandDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];
    }

    public function testShouldDispatchACommandToTheHandlerTheGatewayDeclares(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $result = $gateway->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertInstanceOf(CaptureResult::class, $result);
        $this->assertSame(PaymentStatus::Pending, $result->status);
    }

    public function testShouldReturnTheNextActionRatherThanThrowIt(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $result = $gateway->execute(CaptureCommand::forPayment($this->buildPayment()));

        $this->assertInstanceOf(Redirect::class, $result->next);
        $this->assertSame('https://acme.test/checkout/tok_1', $result->next->url);
        $this->assertTrue($result->requiresInteraction());
    }

    public function testShouldResumeFromPersistedStateWhenTheSameCommandRunsAgain(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');
        $payment = $this->buildPayment();

        // First pass: no PSP state, so the handler opens the checkout and sends the customer away.
        $first = $gateway->execute(CaptureCommand::forPayment($payment));

        $this->assertInstanceOf(Redirect::class, $first->next);
        $this->assertSame('tok_1', $payment->getDetails()['psp_token']);

        // The customer comes back to the same URL and the identical command runs again. What tells the
        // handler it is now on its second pass is the state written during the first.
        $second = $gateway->execute(CaptureCommand::forPayment($payment));

        $this->assertNotInstanceOf(NextAction::class, $second->next);
        $this->assertSame(PaymentStatus::Captured, $second->status);
        $this->assertSame('txn_1', $second->transactionId);
    }

    public function testShouldWriteHandlerStateOntoThePayment(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');
        $payment = $this->buildPayment();

        $gateway->execute(CaptureCommand::forPayment($payment));

        $this->assertSame([
            'psp_token' => 'tok_1',
        ], $payment->getDetails());
    }

    public function testShouldThrowForACommandTheGatewayDeclaresNoHandlerFor(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $this->expectException(CommandNotSupportedException::class);
        $this->expectExceptionMessage(RefundCommand::class);

        $gateway->execute(RefundCommand::forPayment($this->buildPayment()));
    }

    public function testShouldReportWhichCommandsItSupports(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');

        $this->assertInstanceOf(Gateway::class, $gateway);
        $this->assertTrue($gateway->supportsCommand(CaptureCommand::class));
        $this->assertFalse($gateway->supportsCommand(RefundCommand::class));
    }

    public function testShouldGiveTheHandlerTheGatewayItBelongsTo(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');
        $payment = $this->buildPayment();

        $gateway->execute(CaptureCommand::forPayment($payment));

        $this->assertSame('Acme Payments', AcmeCaptureHandler::$seenGatewayName);
    }

    public function testShouldAutowireTheApiFromTheConfig(): void
    {
        $gateway = $this->buildPayum()->getGateway('acme');
        $payment = $this->buildPayment();

        $gateway->execute(CaptureCommand::forPayment($payment));

        // Nothing declared the Api: its constructor takes only container entries, so PHP-DI built it.
        $this->assertSame('sk_test', AcmeCaptureHandler::$seenSecret);
    }

    private function buildPayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);

        return $payment;
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(): Payum
    {
        AcmeCaptureHandler::$seenGatewayName = null;
        AcmeCaptureHandler::$seenSecret = null;

        return (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('acme', new AcmeConfig('sk_test'))
            ->getPayum();
    }
}

final class AcmeApi
{
    public function __construct(
        private readonly AcmeConfig $config
    ) {
    }

    public function secret(): string
    {
        return $this->config->secretKey;
    }
}

final class AcmeConfig implements GatewayConfig
{
    public function __construct(
        public readonly string $secretKey
    ) {
    }

    public function getGatewayClass(): string
    {
        return AcmeGateway::class;
    }
}

final class AcmeGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AcmeConfig::class;
    }

    public function handlers(): array
    {
        return [AcmeCaptureHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme Payments';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class AcmeCaptureHandler implements CaptureHandlerInterface
{
    public static ?string $seenGatewayName = null;

    public static ?string $seenSecret = null;

    public function __construct(
        private readonly AcmeApi $api
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        self::$seenGatewayName = $context->gateway()->name();
        self::$seenSecret = $this->api->secret();

        $state = $context->state();

        if ($state['psp_token']) {
            return CaptureResult::captured('txn_1');
        }

        $state['psp_token'] = 'tok_1';

        return CaptureResult::pending(new Redirect('https://acme.test/checkout/' . $state['psp_token']));
    }
}
