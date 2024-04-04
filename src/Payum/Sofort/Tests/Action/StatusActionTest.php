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

    public function testShouldMarkExpiredIfPaymentExpirationTimePassed()
    {
        $expires = time();
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'expires' => --$expires
        ));

        $this->assertTrue($request->isExpired());
    }

    public function testShouldMarkNewIfPaymentWithoutTransactionId()
    {
        $request = $this->executeRequestWithDetails(array());

        $this->assertTrue($request->isNew());

        $request = $this->executeRequestWithDetails(array('transaction_id'));

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkNewIfPaymentWithoutStatus()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID
        ));

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkFailedIfPaymentLoss()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_LOSS
        ));

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkPendingIfPaymentPending()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_PENDING
        ));

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkUnknownIfPaymentReceivedAndPartiallyCredited()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_RECEIVED,
            'statusReason' => Api::SUB_PARTIALLY
        ));

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkCapturedIfPaymentReceivedAndCredited()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_RECEIVED,
            'statusReason' => Api::SUB_CREDITED
        ));

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkCapturedIfPaymentReceivedWithOverpayment()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_RECEIVED,
            'statusReason' => Api::SUB_OVERPAYMENT
        ));

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkUnknownIfPaymentRefundedPartially()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_REFUNDED,
            'statusReason' => Api::SUB_COMPENSATION
        ));

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkRefundedIfPaymentRefunded()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_REFUNDED,
            'statusReason' => Api::SUB_REFUNDED
        ));

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkCapturedIfPaymentUntraceable()
    {
        $request = $this->executeRequestWithDetails(array(
            'transaction_id' => self::TRANSACTION_ID,
            'status' => Api::STATUS_UNTRACEABLE
        ));

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkUnknownIfPaymentWithUnsupportedStatus()
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
