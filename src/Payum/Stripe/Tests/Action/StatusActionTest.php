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

    public function testShouldMarkNewIfDetailsEmpty(): void
    {
        $action = new StatusAction();

        $model = [];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkFailedIfDetailsHasErrorSet(): void
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

    public function testShouldMarkPendingIfModelHasNotStatusButHasCard(): void
    {
        $action = new StatusAction();

        $model = [
            'card' => 'aCard',
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isPending());
    }

    public function testShouldMarkFailedIfStatusFailed(): void
    {
        $action = new StatusAction();

        $model = [
            'status' => Constants::STATUS_FAILED,
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkRefundedIfStatusSetAndRefundedTrue(): void
    {
        $action = new StatusAction();

        $model = [
            'status' => Constants::STATUS_SUCCEEDED,
            'refunded' => true,
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isRefunded());
    }

    public function testShouldNotMarkRefundedIfStatusNotSetAndRefundedTrue(): void
    {
        $action = new StatusAction();

        $model = [
            'refunded' => true,
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertFalse($status->isRefunded());
        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkCapturedIfStatusSucceededAndCaptureAndPaidSetTrue(): void
    {
        $action = new StatusAction();

        $model = [
            'status' => Constants::STATUS_SUCCEEDED,
            'captured' => true,
            'paid' => true,
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldNotMarkCapturedIfStatusSucceededAndCaptureSetTrueButPaidNotTrue(): void
    {
        $action = new StatusAction();

        $model = [
            'status' => Constants::STATUS_SUCCEEDED,
            'captured' => true,
            'paid' => false,
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertFalse($status->isCaptured());
        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkCapturedIfStatusPaidAndCaptureAndPaidSetTrue(): void
    {
        $action = new StatusAction();

        $model = [
            'status' => Constants::STATUS_PAID,
            'captured' => true,
            'paid' => true,
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldNotMarkCapturedIfStatusPaidAndCaptureSetTrueButPaidNotTrue(): void
    {
        $action = new StatusAction();

        $model = [
            'status' => Constants::STATUS_PAID,
            'captured' => true,
            'paid' => false,
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertFalse($status->isCaptured());
        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkAuthorizedIfStatusSucceededAndCaptureSetFalse(): void
    {
        $action = new StatusAction();

        $model = [
            'status' => Constants::STATUS_SUCCEEDED,
            'captured' => false,
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isAuthorized());
    }

    public function testShouldMarkAuthorizedIfStatusPaidAndCaptureSetFalse(): void
    {
        $action = new StatusAction();

        $model = [
            'status' => Constants::STATUS_PAID,
            'captured' => false,
        ];

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isAuthorized());
    }

    public function testShouldMarkUnknownIfStatusCouldBeGuessed(): void
    {
        $action = new StatusAction();

        $model = [
            'status' => 'unknown',
        ];

        $status = new GetHumanStatus($model);
        $status->markPending();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }
}
