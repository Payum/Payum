<?php

declare(strict_types=1);

namespace Payum\Offline\Tests\Handler;

use Payum\Core\Command\RefundCommand;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\RefundResult;

final class RefundHandlerTest extends OfflineHandlerTestCase
{
    public function testShouldRefundACapturedPaymentInFull(): void
    {
        $payment = $this->payment([
            'status' => 'captured',
        ]);

        $result = $this->gateway()->execute(RefundCommand::forPayment($payment));

        $this->assertInstanceOf(RefundResult::class, $result);
        $this->assertSame(PaymentStatus::Refunded, $result->status);
        $this->assertSame(123, $result->refundedAmount);
        $this->assertSame('refunded', $payment->getDetails()['status']);
        $this->assertSame(123, $payment->getDetails()['refunded_amount']);
    }

    public function testShouldRefundPartOfAPaymentThenTheRest(): void
    {
        $gateway = $this->gateway();
        $payment = $this->payment([
            'status' => 'captured',
        ]);

        $first = $gateway->execute(RefundCommand::forPayment($payment, 23));

        $this->assertInstanceOf(RefundResult::class, $first);
        $this->assertSame(PaymentStatus::PartiallyRefunded, $first->status);
        $this->assertSame(23, $first->refundedAmount);

        $rest = $gateway->execute(RefundCommand::forPayment($payment));

        $this->assertInstanceOf(RefundResult::class, $rest);
        $this->assertSame(PaymentStatus::Refunded, $rest->status);
        $this->assertSame(100, $rest->refundedAmount);
    }

    public function testShouldDeclineAPaymentThatWasNotCaptured(): void
    {
        $payment = $this->payment([
            'status' => 'pending',
        ]);

        $result = $this->gateway()->execute(RefundCommand::forPayment($payment));

        $this->assertNull($result->status);
        $this->assertSame(FailureReason::Declined, $result->failure?->reason);
        $this->assertSame('pending', $payment->getDetails()['status']);
    }

    public function testShouldDeclineMoreThanIsLeftToRefund(): void
    {
        $payment = $this->payment([
            'status' => 'partially_refunded',
            'refunded_amount' => 100,
        ]);

        $result = $this->gateway()->execute(RefundCommand::forPayment($payment, 50));

        $this->assertSame('amount_exceeds_balance', $result->failure?->code);
        $this->assertSame(100, $payment->getDetails()['refunded_amount']);
    }
}
