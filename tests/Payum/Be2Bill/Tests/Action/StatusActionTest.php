<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Action\StatusAction;
use Payum\Be2Bill\PaymentInstruction;
use Payum\Request\StatusRequestInterface;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Action\StatusAction');
        
        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionInterface'));
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
    public function shouldSupportStatusRequestAndPaymentInstructionAsModel()
    {
        $action = new StatusAction();

        $request = $this->createStatusRequestStub(new PaymentInstruction);
        
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestPaymentInstructionAggregate()
    {
        $action = new StatusAction();

        $model = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $model
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue(new PaymentInstruction))
        ;

        $request = $this->createStatusRequestStub($model);

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
    public function shouldNotSupportStatusRequestAndNotPaymentInstructionAsModel()
    {
        $action = new StatusAction();

        $request = $this->createStatusRequestStub(new \stdClass);
        
        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StatusRequestInterface
     */
    protected function createStatusRequestStub($model)
    {
        $status = $this->getMock('Payum\Request\StatusRequestInterface');

        $status
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;
        
        return $status;
    }
}