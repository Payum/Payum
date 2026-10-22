<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Http\Discovery\Psr17FactoryDiscovery;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Legacy\Handler\NotifyActionHandler;
use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;
use Payum\Core\Tests\Bc\Fixture\AwkwardAdaptedConfig;
use PHPUnit\Framework\TestCase;

final class ActionToHandlerAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];
    }

    public function testShouldRefuseToDropWhatTheActionCannotTake(): void
    {
        $gateway = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('adapted', new AwkwardAdaptedConfig())
            ->getPayum()
            ->getGateway('adapted')
        ;

        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('EUR');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('an amount');

        // Capturing everything instead of the part asked for is the failure this prevents.
        $gateway->execute(CaptureCommand::forPayment($payment, 50));
    }

    public function testShouldHandWhatThePspPostedToWhoeverHearsOfTheWebhook(): void
    {
        $request = Psr17FactoryDiscovery::findServerRequestFactory()
            ->createServerRequest('POST', 'https://payum.dev/notify.php')
            ->withParsedBody([
                'event' => 'paid',
            ]);

        $event = (new NotifyActionHandler())->verify($request);

        // The action still checks the message itself, when it handles it.
        $this->assertFalse($event->isVerified());
        $this->assertSame([
            'event' => 'paid',
        ], $event->payload);
    }
}
