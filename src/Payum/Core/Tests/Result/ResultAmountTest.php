<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Result;

use Money\Money;
use Payum\Core\Result\AuthorizeResult;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\PayoutResult;
use Payum\Core\Result\RefundResult;
use PHPUnit\Framework\TestCase;

/**
 * Every result that reports an amount reports it both ways, so a handler can move to Money without the
 * application that reads its results having to.
 */
final class ResultAmountTest extends TestCase
{
    public function testShouldReportACaptureBothWays(): void
    {
        $result = CaptureResult::captured('txn_1', Money::JPY(500));

        $this->assertTrue(Money::JPY(500)->equals($result->capturedMoney));
        $this->assertSame(500, $result->capturedAmount);
    }

    public function testShouldReportAnAuthorizationBothWays(): void
    {
        $result = AuthorizeResult::authorized('txn_1', Money::USD(1500));

        $this->assertTrue(Money::USD(1500)->equals($result->authorizedMoney));
        $this->assertSame(1500, $result->authorizedAmount);
    }

    public function testShouldReportARefundBothWays(): void
    {
        $result = RefundResult::partiallyRefunded('txn_1', Money::EUR(250));

        $this->assertTrue(Money::EUR(250)->equals($result->refundedMoney));
        $this->assertSame(250, $result->refundedAmount);
    }

    public function testShouldReportAPayoutBothWays(): void
    {
        $result = PayoutResult::paidOut('txn_1', Money::GBP(999));

        $this->assertTrue(Money::GBP(999)->equals($result->paidOutMoney));
        $this->assertSame(999, $result->paidOutAmount);
    }

    public function testShouldLeaveTheMoneyUnsetForAHandlerThatReportedMinorUnits(): void
    {
        $result = CaptureResult::captured('txn_1', 500);

        $this->assertSame(500, $result->capturedAmount);
        $this->assertNull($result->capturedMoney);
    }

    public function testShouldReportNeitherWhenTheHandlerReportedNoAmount(): void
    {
        $result = CaptureResult::captured('txn_1');

        $this->assertNull($result->capturedAmount);
        $this->assertNull($result->capturedMoney);
    }
}
