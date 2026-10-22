<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\HandlerToActionAdapter;
use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Refund;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\Redirect;
use PHPUnit\Framework\TestCase;

final class HandlerToActionAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];
    }

    public function testShouldThrowTheReplyAOneXCallerCatchesForWhatTheHandlerDecided(): void
    {
        $gateway = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('handled', static fn (array $config, GatewayFactoryInterface $core) => new HandledGatewayFactory($config, $core))
            ->addGateway('handled', [
                'factory' => 'handled',
            ])
            ->getPayum()
            ->getGateway('handled')
        ;

        try {
            $gateway->execute(new Capture($this->buildPayment()));
            $this->fail('The handler asked for a redirect.');
        } catch (HttpRedirect $reply) {
            $this->assertSame('https://psp.test/checkout', $reply->getUrl());
        }
    }

    public function testShouldLeaveARequestForAnotherCommandToTheActions(): void
    {
        $adapter = new HandlerToActionAdapter(new RedirectingCaptureHandler());

        $this->assertTrue($adapter->supports(new Capture($this->buildPayment())));
        $this->assertFalse($adapter->supports(new Refund($this->buildPayment())));
    }

    public function testShouldLeaveARequestCarryingOnlyDetailsToTheActions(): void
    {
        $adapter = new HandlerToActionAdapter(new RedirectingCaptureHandler());

        // A handler works on a payment. Details alone are what a 1.x capture action takes.
        $this->assertFalse($adapter->supports(new Capture(new ArrayObject())));
    }

    public function testShouldSaySoWhenTheGatewayCannotRunAHandler(): void
    {
        $adapter = new HandlerToActionAdapter(new RedirectingCaptureHandler());
        $adapter->setGateway($this->createMock(GatewayInterface::class));

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('has to be a Payum\Core\Gateway');

        $adapter->execute(new Capture($this->buildPayment()));
    }

    private function buildPayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('EUR');

        return $payment;
    }
}

final class HandledGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'handled',
            'payum.factory_title' => 'Handled',
            'payum.action.capture' => new HandlerToActionAdapter(new RedirectingCaptureHandler()),
        ]);
    }
}

final class RedirectingCaptureHandler implements CaptureHandlerInterface
{
    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        return CaptureResult::pending(new Redirect('https://psp.test/checkout'));
    }
}
