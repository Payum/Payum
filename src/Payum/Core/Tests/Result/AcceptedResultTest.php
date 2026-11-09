<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Result;

use Payum\Core\Result\AuthorizeResult;
use Payum\Core\Result\CancelResult;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\PayoutResult;
use Payum\Core\Result\RefundResult;
use Payum\Core\Result\Result;
use Payum\Core\Result\SyncResult;
use PHPUnit\Framework\TestCase;

/**
 * A refund, a void or a capture of an authorization that the PSP took on but has not concluded. The
 * payment keeps its status until the webhook says how it ended.
 */
final class AcceptedResultTest extends TestCase
{
    /**
     * @return iterable<string, array{callable(string, array<string, mixed>): Result}>
     */
    public static function provideAcceptedResults(): iterable
    {
        yield 'refund' => [RefundResult::accepted(...)];
        yield 'cancel' => [CancelResult::accepted(...)];
        yield 'capture' => [CaptureResult::accepted(...)];
    }

    /**
     * @dataProvider provideAcceptedResults
     *
     * @param callable(string, array<string, mixed>): Result $accepted
     */
    public function testShouldLeaveTheStatusAloneAndKeepThePspRequest(callable $accepted): void
    {
        $result = $accepted('req_1', [
            'pspReference' => 'req_1',
        ]);

        $this->assertTrue($result->isAccepted());
        $this->assertNull($result->status);
        $this->assertSame('req_1', $result->transactionId);
        $this->assertSame([
            'pspReference' => 'req_1',
        ], $result->raw);
        $this->assertNull($result->next);
        $this->assertNull($result->failure);
        $this->assertFalse($result->isSuccessful());
        $this->assertFalse($result->isFailed());
    }

    /**
     * @return iterable<string, array{Result}>
     */
    public static function provideResultsThatWereNotAccepted(): iterable
    {
        $declined = new Failure(FailureReason::Declined, 'declined');

        yield 'refunded' => [RefundResult::refunded('re_1')];
        yield 'declined refund' => [RefundResult::failed($declined)];
        yield 'refund built by hand' => [new RefundResult(null)];
        yield 'canceled' => [CancelResult::canceled()];
        yield 'declined cancel' => [CancelResult::failed($declined)];
        yield 'captured' => [CaptureResult::captured('ch_1')];
        yield 'pending capture' => [CaptureResult::pending()];
        yield 'pending authorization' => [AuthorizeResult::pending()];
        yield 'pending payout' => [PayoutResult::pending()];
        yield 'synced' => [SyncResult::synced(PaymentStatus::Captured)];
        yield 'ignored notification' => [NotifyResult::ignored()];
    }

    /**
     * @dataProvider provideResultsThatWereNotAccepted
     */
    public function testShouldOnlyBeAcceptedWhenBuiltAsAccepted(Result $result): void
    {
        $this->assertFalse($result->isAccepted());
    }
}
