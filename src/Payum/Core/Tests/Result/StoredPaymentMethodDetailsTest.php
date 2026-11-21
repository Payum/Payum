<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Result;

use Payum\Core\Model\PaymentMethodType;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\StoredPaymentMethodStatus;
use PHPUnit\Framework\TestCase;

final class StoredPaymentMethodDetailsTest extends TestCase
{
    public function testActiveShouldCarryTheReference(): void
    {
        $details = StoredPaymentMethodDetails::active('pm_1', PaymentMethodType::Card, last4: '4242');

        $this->assertSame('pm_1', $details->reference);
        $this->assertSame(PaymentMethodType::Card, $details->type);
        $this->assertSame(StoredPaymentMethodStatus::Active, $details->status);
        $this->assertSame('4242', $details->last4);
        $this->assertNull($details->brand);
    }

    public function testRevokedShouldChangeOnlyTheStatus(): void
    {
        $details = StoredPaymentMethodDetails::revoked();

        $this->assertSame(StoredPaymentMethodStatus::Revoked, $details->status);
        $this->assertNull($details->reference);
        $this->assertNull($details->type);
        $this->assertNull($details->last4);
        $this->assertNull($details->gatewayCustomerId);
    }
}
