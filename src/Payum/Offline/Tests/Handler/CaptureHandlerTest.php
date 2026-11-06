<?php

declare(strict_types=1);

namespace Payum\Offline\Tests\Handler;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Result\PaymentStatus;
use Payum\Offline\OfflineConfig;

final class CaptureHandlerTest extends OfflineHandlerTestCase
{
    public function testShouldCaptureAndWriteThePaymentToTheDetails(): void
    {
        $payment = $this->payment();

        $result = $this->gateway()->execute(CaptureCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame([
            'amount' => 123,
            'currency' => 'EUR',
            'number' => 'order-1',
            'description' => 'Order 1',
            'client_email' => 'customer@example.com',
            'client_id' => 'customer-1',
            'paid' => true,
            'status' => 'captured',
        ], (array) $payment->getDetails());
    }

    public function testShouldLeaveAnUnpaidPaymentPending(): void
    {
        $payment = $this->payment([
            'paid' => false,
        ]);

        $result = $this->gateway()->execute(CaptureCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Pending, $result->status);
        $this->assertSame('pending', $payment->getDetails()['status']);
    }

    public function testShouldCaptureOncePaidWhenTheConfigStartsPaymentsUnpaid(): void
    {
        $gateway = $this->gateway(new OfflineConfig(paid: false));
        $payment = $this->payment();

        $this->assertSame(PaymentStatus::Pending, $gateway->execute(CaptureCommand::forPayment($payment))->status);

        $payment->setDetails([
            'paid' => true,
        ] + (array) $payment->getDetails());

        $this->assertSame(PaymentStatus::Captured, $gateway->execute(CaptureCommand::forPayment($payment))->status);
    }

    public function testShouldRefuseAPartialCapture(): void
    {
        $this->expectException(LogicException::class);

        $this->gateway()->execute(CaptureCommand::forPayment($this->payment(), 50));
    }
}
