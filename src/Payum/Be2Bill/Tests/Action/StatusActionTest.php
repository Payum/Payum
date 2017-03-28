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

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array()));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfExecCodeNotSet()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array()));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfExecCodeSuccessful()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'EXECCODE' => Api::EXECCODE_SUCCESSFUL,
        )));

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfExecCodeFailed()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'EXECCODE' => Api::EXECCODE_BANK_ERROR,
        )));

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfExecCodeTimeOut()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'EXECCODE' => Api::EXECCODE_TIME_OUT,
        )));

        $this->assertTrue($status->isUnknown());
    }
}
