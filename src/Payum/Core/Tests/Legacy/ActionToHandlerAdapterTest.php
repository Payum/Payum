<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\LogicException;
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
}
