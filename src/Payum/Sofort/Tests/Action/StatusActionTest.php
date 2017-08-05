<?php

namespace Payum\Sofort\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use Payum\Sofort\Action\StatusAction;
use Payum\Sofort\Api;

class StatusActionTest extends GenericActionTest
{
    const TRANSACTION_ID = '1597-FS16-234D-A324';

    protected $requestClass = GetHumanStatus::class;

    protected $actionClass = StatusAction::class;

    /**
     * @test
     */
    public function shouldMarkExpiredIfPaymentExpirationTimePassed()
    {
        $expires = time();
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'expires' => --$expires
        ));

        $this->assertTrue($request->isExpired());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfPaymentWithoutTransactionId()
    {
        $request = $this->executeRequestWithDetails(array());

        $this->assertTrue($request->isNew());

        $request = $this->executeRequestWithDetails(array('transaction_id'));

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfPaymentWithoutStatus()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID
        ));

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfPaymentLoss()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_LOSS
        ));

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfPaymentPending()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_PENDING
        ));

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfPaymentReceivedAndPartiallyCredited()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_RECEIVED,
            'statusReason' => Api::SUB_PARTIALLY
        ));

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfPaymentReceivedAndCredited()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_RECEIVED,
            'statusReason' => Api::SUB_CREDITED
        ));

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfPaymentReceivedWithOverpayment()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_RECEIVED,
            'statusReason' => Api::SUB_OVERPAYMENT
        ));

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfPaymentRefundedPartially()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_REFUNDED,
            'statusReason' => Api::SUB_COMPENSATION
        ));

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkRefundedIfPaymentRefunded()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_REFUNDED,
            'statusReason' => Api::SUB_REFUNDED
        ));

        $this->assertTrue($request->isRefunded());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfPaymentUntraceable()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_UNTRACEABLE
        ));

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfPaymentWithUnsupportedStatus()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => 'unsupported'
        ));

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @param array $details
     * @return GetHumanStatus
     */
    private function executeRequestWithDetails($details)
    {
        $action = new StatusAction();
        $request = new GetHumanStatus($details);
        $action->execute($request);

        return $request;
    }
}
