<?php

namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Action\StatusAction;
use Payum\Be2Bill\Api;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;

class StatusActionTest extends GenericActionTest
{
    protected $actionClass = StatusAction::class;

    protected $requestClass = GetHumanStatus::class;

    public function testShouldMarkNewIfDetailsEmpty(): void
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([]));

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkNewIfExecCodeNotSet(): void
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([]));

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkCapturedIfExecCodeSuccessful(): void
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'EXECCODE' => Api::EXECCODE_SUCCESSFUL,
        ]));

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkFailedIfExecCodeFailed(): void
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'EXECCODE' => Api::EXECCODE_BANK_ERROR,
        ]));

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkUnknownIfExecCodeTimeOut(): void
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus([
            'EXECCODE' => Api::EXECCODE_TIME_OUT,
        ]));

        $this->assertTrue($status->isUnknown());
    }
}
