<?php

declare(strict_types=1);

namespace Payum\Offline\Tests\Handler;

use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;
use Payum\Offline\OfflineConfig;
use PHPUnit\Framework\TestCase;

abstract class OfflineHandlerTestCase extends TestCase
{
    protected function gateway(OfflineConfig $config = new OfflineConfig()): GatewayInterface
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('offline', $config)
            ->getPayum()
            ->getGateway('offline');
    }

    /**
     * @param array<string, mixed> $details
     */
    protected function payment(array $details = []): Payment
    {
        $payment = new Payment();
        $payment->setNumber('order-1');
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);
        $payment->setDescription('Order 1');
        $payment->setClientEmail('customer@example.com');
        $payment->setClientId('customer-1');
        $payment->setDetails($details);

        return $payment;
    }
}
