<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Result;

use Money\Money;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\SyncResult;
use PHPUnit\Framework\TestCase;

final class PaymentStatusTest extends TestCase
{
    /**
     * Backing values are what applications persist, so changing one orphans stored rows.
     */
    public function testShouldKeepItsBackingValues(): void
    {
        $this->assertSame([
            'Authorized' => 'authorized',
            'Canceled' => 'canceled',
            'Captured' => 'captured',
            'ChargedBack' => 'charged_back',
            'Disputed' => 'disputed',
            'Expired' => 'expired',
            'Failed' => 'failed',
            'New' => 'new',
            'PartiallyCaptured' => 'partially_captured',
            'PartiallyRefunded' => 'partially_refunded',
            'PaidOut' => 'payedout',
            'Pending' => 'pending',
            'Refunded' => 'refunded',
            'Suspended' => 'suspended',
            'Unknown' => 'unknown',
        ], array_column(PaymentStatus::cases(), 'value', 'name'));
    }

    public function testShouldReportAPartialCapture(): void
    {
        $result = CaptureResult::partiallyCaptured('txn_1', Money::EUR(400));

        $this->assertSame(PaymentStatus::PartiallyCaptured, $result->status);
        $this->assertSame('txn_1', $result->transactionId);
        $this->assertSame(400, $result->capturedAmount);
        $this->assertTrue($result->isSuccessful());
    }

    public function testShouldNotCallADisputedPaymentSuccessful(): void
    {
        $result = SyncResult::synced(PaymentStatus::Disputed);

        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->isFailed());
    }

    public function testShouldNotCallAChargedBackPaymentSuccessful(): void
    {
        $result = SyncResult::synced(PaymentStatus::ChargedBack);

        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->isFailed());
    }
}
