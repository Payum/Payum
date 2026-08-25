<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\HandlerToActionAdapter;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Refund;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * A handler reached from a gateway a 1.x factory still assembles.
 */
final class HandlerToActionAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/pay',
        ];

        $_GET = [];
        $_POST = [];
    }

    public function testShouldAnswerTheOneXRequestFromTheHandlerBehindIt(): void
    {
        $payment = $this->buildPayment();

        try {
            $this->buildPayum()->getGateway('acme')->execute(new Capture($payment));

            $this->fail('The handler asked for a redirect, so a reply was expected.');
        } catch (HttpRedirect $reply) {
            $this->assertSame('https://psp.test/checkout', $reply->getUrl());
        }

        // The pipeline the handler is entitled to ran: what it wrote is on the payment, and the status
        // it declared was recorded.
        $this->assertSame('chk_1', $payment->getDetails()['checkout_id']);
        $this->assertSame(PaymentStatus::Pending, $payment->getStatus());
    }

    public function testShouldReturnTheReplyWhenTheCallerAsksToCatchIt(): void
    {
        $reply = $this->buildPayum()->getGateway('acme')->execute(new Capture($this->buildPayment()), true);

        $this->assertInstanceOf(HttpRedirect::class, $reply);
    }

    public function testShouldThrowNothingOnceTheHandlerIsFinished(): void
    {
        $payment = $this->buildPayment();
        $payment->setDetails([
            'checkout_id' => 'chk_1',
        ]);

        $this->buildPayum()->getGateway('acme')->execute(new Capture($payment));

        $this->assertSame(PaymentStatus::Captured, $payment->getStatus());
    }

    public function testShouldGiveTheHandlerTheTokenFactoryTheGatewayCarries(): void
    {
        $payment = $this->buildPayment();

        $this->buildPayum()->getGateway('acme')->execute(new Capture($payment), true);

        // The 1.x extension that hands the token factory out reaches the adapter, so a handler that has
        // to mint a notify url can. Without it, tokens() throws rather than returning null.
        $this->assertTrue($payment->getDetails()['has_tokens']);
    }

    public function testShouldBuildTheAdapterFromTheFactoryConfigTheOtherActionsUse(): void
    {
        $payment = $this->buildPayment();

        $this->buildPayum()->getGateway('acme')->execute(new Capture($payment), true);

        // Registered as a closure over the config, the way a 1.x factory builds anything that needs the
        // api, so a handler is configured from exactly what the actions beside it are configured from.
        $this->assertSame('sk_test', $payment->getDetails()['api_key']);
    }

    public function testShouldLeaveARequestItsHandlerDoesNotAnswerAlone(): void
    {
        // The adapter answers exactly the one command its handler handles, so a gateway can adopt handlers
        // one operation at a time.
        $gateway = $this->buildPayum()->getGateway('acme');

        $gateway->execute(new Refund($this->buildPayment()));

        $this->assertTrue(AdapterRefundAction::$ran);
    }

    private function buildPayment(): AdapterTrackedPayment
    {
        $payment = new AdapterTrackedPayment();
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
        AdapterRefundAction::$ran = false;

        return (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory(
                'acme_factory',
                static fn (array $config, GatewayFactoryInterface $coreGatewayFactory): AdapterGatewayFactory => new AdapterGatewayFactory($config, $coreGatewayFactory)
            )
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum();
    }
}

/**
 * Untouched 1.x wiring, except for the one action that has become a handler.
 */
final class AdapterGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'acme_factory',
            'payum.factory_title' => 'Acme',
            'payum.api' => 'sk_test',
            'payum.action.capture' => static fn (ArrayObject $config): HandlerToActionAdapter => new HandlerToActionAdapter(
                new AdapterCaptureHandler($config['payum.api']),
            ),
            'payum.action.refund' => new AdapterRefundAction(),
        ]);
    }
}

final class AdapterCaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly string $apiKey
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $state = $context->state();

        if (! $state['checkout_id']) {
            $state['checkout_id'] = 'chk_1';
            // Throws when the gateway carries no token factory, which is the thing being asserted.
            $context->tokens();
            $state['has_tokens'] = true;
            $state['api_key'] = $this->apiKey;

            return CaptureResult::pending(new Redirect('https://psp.test/checkout'));
        }

        return CaptureResult::captured('txn_1');
    }
}

final class AdapterRefundAction implements ActionInterface
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

class AdapterTrackedPayment extends Payment implements StatusAwareInterface
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
