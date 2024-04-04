<?php
namespace Payum\Stripe\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\StatusAction;
use Payum\Stripe\Constants;

class StatusActionTest extends GenericActionTest
{
    protected $requestClass = GetHumanStatus::class;

    protected $actionClass = StatusAction::class;

    public function testShouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $model = [];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkFailedIfDetailsHasErrorSet()
    {
        $action = new StatusAction();

        $model = [
            'error' => [
                'type' => 'invalid_request_error',
                'message' => 'Amount must be at least 50 cents',
                'param' => 'amount',
            ],
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkPendingIfModelHasNotStatusButHasCard()
    {
        $action = new StatusAction();

        $model = array(
            'card' => 'aCard',
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isPending());
    }

    public function testShouldMarkFailedIfStatusFailed()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_FAILED,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkRefundedIfStatusSetAndRefundedTrue()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_SUCCEEDED,
            'refunded' => true,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isRefunded());
    }

    public function testShouldNotMarkRefundedIfStatusNotSetAndRefundedTrue()
    {
        $action = new StatusAction();

        $model = array(
            'refunded' => true,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertFalse($status->isRefunded());
        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkCapturedIfStatusSucceededAndCaptureAndPaidSetTrue()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_SUCCEEDED,
            'captured' => true,
            'paid' => true,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldNotMarkCapturedIfStatusSucceededAndCaptureSetTrueButPaidNotTrue()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_SUCCEEDED,
            'captured' => true,
            'paid' => false,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertFalse($status->isCaptured());
        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkCapturedIfStatusPaidAndCaptureAndPaidSetTrue()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_PAID,
            'captured' => true,
            'paid' => true,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldNotMarkCapturedIfStatusPaidAndCaptureSetTrueButPaidNotTrue()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_PAID,
            'captured' => true,
            'paid' => false,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertFalse($status->isCaptured());
        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkAuthorizedIfStatusSucceededAndCaptureSetFalse()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_SUCCEEDED,
            'captured' => false,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isAuthorized());
    }

    public function testShouldMarkAuthorizedIfStatusPaidAndCaptureSetFalse()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_PAID,
            'captured' => false,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isAuthorized());
    }

    public function testShouldMarkUnknownIfStatusCouldBeGuessed()
    {
        $action = new StatusAction();

        $model = array(
            'status' => 'unknown',
        );

        $status = new GetHumanStatus($model);
        $status->markPending();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }
}
