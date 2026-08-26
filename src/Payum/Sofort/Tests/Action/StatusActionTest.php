<?php

declare(strict_types=1);

namespace Payum\Sofort\Tests\Action;

use Payum\Core\Clock\SystemClock;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use Payum\Core\Tests\Mocks\Clock\FrozenClock;
use Payum\Sofort\Action\StatusAction;
use Payum\Sofort\Api;
use Psr\Clock\ClockInterface;

final class StatusActionTest extends GenericActionTest
{
    public const TRANSACTION_ID = '1597-FS16-234D-A324';

    protected $requestClass = GetHumanStatus::class;

    protected $actionClass = StatusAction::class;

    public function testShouldMarkExpiredIfPaymentExpirationTimePassed(): void
    {
        $clock = new FrozenClock('2026-01-01 12:00:00');

        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'expires' => $clock->now()->getTimestamp() - 1,
        ], $clock);

        $this->assertTrue($request->isExpired());
    }

    public function testShouldNotMarkExpiredWhileTheExpirationTimeIsStillAhead(): void
    {
        $clock = new FrozenClock('2026-01-01 12:00:00');

        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'expires' => $clock->now()->getTimestamp() + 1,
        ], $clock);

        $this->assertFalse($request->isExpired());
        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkNewIfPaymentWithoutTransactionId(): void
    {
        $request = $this->executeRequestWithDetails([]);

        $this->assertTrue($request->isNew());

        $request = $this->executeRequestWithDetails(['transaction_id']);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkNewIfPaymentWithoutStatus(): void
    {
        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
        ]);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkFailedIfPaymentLoss(): void
    {
        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_LOSS,
        ]);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkPendingIfPaymentPending(): void
    {
        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_PENDING,
        ]);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkUnknownIfPaymentReceivedAndPartiallyCredited(): void
    {
        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_RECEIVED,
            'statusReason' => Api::SUB_PARTIALLY,
        ]);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkCapturedIfPaymentReceivedAndCredited(): void
    {
        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_RECEIVED,
            'statusReason' => Api::SUB_CREDITED,
        ]);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkCapturedIfPaymentReceivedWithOverpayment(): void
    {
        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_RECEIVED,
            'statusReason' => Api::SUB_OVERPAYMENT,
        ]);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkUnknownIfPaymentRefundedPartially(): void
    {
        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_REFUNDED,
            'statusReason' => Api::SUB_COMPENSATION,
        ]);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkRefundedIfPaymentRefunded(): void
    {
        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_REFUNDED,
            'statusReason' => Api::SUB_REFUNDED,
        ]);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkCapturedIfPaymentUntraceable(): void
    {
        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_UNTRACEABLE,
        ]);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkUnknownIfPaymentWithUnsupportedStatus(): void
    {
        $request = $this->executeRequestWithDetails([
            'transaction_id' => self::TRANSACTION_ID,
            'status' => 'unsupported',
        ]);

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @param array $details
     * @return GetHumanStatus
     */
    private function executeRequestWithDetails($details, ClockInterface $clock = new SystemClock())
    {
        $action = new StatusAction($clock);
        $request = new GetHumanStatus($details);
        $action->execute($request);

        return $request;
    }
}
