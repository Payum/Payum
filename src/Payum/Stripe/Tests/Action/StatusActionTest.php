<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\StatusAction;
use Payum\Stripe\Constants;

class StatusActionTest extends GenericActionTest
{
    protected $requestClass = GetHumanStatus::class;

    protected $actionClass = StatusAction::class;

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $model = [];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfDetailsHasErrorSet()
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

    /**
     * @test
     */
    public function shouldMarkPendingIfModelHasNotStatusButHasCard()
    {
        $action = new StatusAction();

        $model = array(
            'card' => 'aCard',
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfStatusFailed()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_FAILED,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkRefundedIfStatusSetAndRefundedTrue()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_SUCCEEDED,
            'refunded' => true,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isRefunded());
    }

    /**
     * @test
     */
    public function shouldNotMarkRefundedIfStatusNotSetAndRefundedTrue()
    {
        $action = new StatusAction();

        $model = array(
            'refunded' => true,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertFalse($status->isRefunded());
        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfStatusSucceededAndCaptureAndPaidSetTrue()
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

    /**
     * @test
     */
    public function shouldNotMarkCapturedIfStatusSucceededAndCaptureSetTrueButPaidNotTrue()
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

    /**
     * @test
     */
    public function shouldMarkCapturedIfStatusPaidAndCaptureAndPaidSetTrue()
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

    /**
     * @test
     */
    public function shouldNotMarkCapturedIfStatusPaidAndCaptureSetTrueButPaidNotTrue()
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

    /**
     * @test
     */
    public function shouldMarkAuthorizedIfStatusSucceededAndCaptureSetFalse()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_SUCCEEDED,
            'captured' => false,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isAuthorized());
    }

    /**
     * @test
     */
    public function shouldMarkAuthorizedIfStatusPaidAndCaptureSetFalse()
    {
        $action = new StatusAction();

        $model = array(
            'status' => Constants::STATUS_PAID,
            'captured' => false,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isAuthorized());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfStatusCouldBeGuessed()
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
