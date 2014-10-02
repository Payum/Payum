<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Api;
use Payum\Core\Request\GetHumanStatus;
use Payum\Be2Bill\Action\StatusAction;
use Payum\Core\Tests\Request\GetStatusInterfaceTest;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Action\StatusAction');
        
        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new StatusAction();
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayAccessAsModel()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus($this->getMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotStatusRequest()
    {
        $action = new StatusAction();
        
        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNotArrayAsModel()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(new \stdClass);
        
        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

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