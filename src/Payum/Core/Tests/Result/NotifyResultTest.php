<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Result;

use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class NotifyResultTest extends TestCase
{
    public function testShouldReportWhatTheEventSaidAboutThePayment(): void
    {
        $result = NotifyResult::handled(PaymentStatus::Captured, transactionId: 'txn_1');

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame('txn_1', $result->transactionId);
        $this->assertTrue($result->isSuccessful());
    }

    public function testShouldCarryTheAnswerThePspInsistsOn(): void
    {
        $result = NotifyResult::handled(PaymentStatus::Captured, Acknowledgement::ok('[accepted]'));

        $this->assertInstanceOf(Acknowledgement::class, $result->acknowledgement);
        $this->assertSame('[accepted]', $result->acknowledgement->body);
    }

    public function testShouldLeaveThePaymentAloneForAnEventItDoesNotCareAbout(): void
    {
        $result = NotifyResult::ignored();

        $this->assertNull($result->status);
        $this->assertNull($result->acknowledgement);
        $this->assertFalse($result->isFailed());
    }

    public function testShouldConcludeNothingWhenNoStatusIsGiven(): void
    {
        $this->assertNull(NotifyResult::handled()->status);
    }
}
