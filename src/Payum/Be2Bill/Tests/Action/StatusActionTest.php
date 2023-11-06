<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Api;
use Payum\Core\Request\GetHumanStatus;
use Payum\Be2Bill\Action\StatusAction;
use Payum\Core\Tests\GenericActionTest;

class StatusActionTest extends GenericActionTest
{
    protected $actionClass = StatusAction::class;

    protected $requestClass = GetHumanStatus::class;

    public function testShouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array()));

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkNewIfExecCodeNotSet()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array()));

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkCapturedIfExecCodeSuccessful()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'EXECCODE' => Api::EXECCODE_SUCCESSFUL,
        )));

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkFailedIfExecCodeFailed()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'EXECCODE' => Api::EXECCODE_BANK_ERROR,
        )));

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkUnknownIfExecCodeTimeOut()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'EXECCODE' => Api::EXECCODE_TIME_OUT,
        )));

        $this->assertTrue($status->isUnknown());
    }
}
