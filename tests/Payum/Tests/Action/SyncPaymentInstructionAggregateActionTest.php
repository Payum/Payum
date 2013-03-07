<?php
namespace Payum\Tests\Action;

use Payum\Action\SyncPaymentInstructionAggregateAction;
use Payum\Request\SyncRequest;

class SyncPaymentInstructionAggregateActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Action\SyncPaymentInstructionAggregateAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new SyncPaymentInstructionAggregateAction();
    }

    /**
     * @test
     */
    public function shouldSupportSyncRequestWithPaymentInstructionAggregateAsModel()
    {
        $modelMock = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue(new \stdClass))
        ;
        
        $action = new SyncPaymentInstructionAggregateAction();

        $this->assertTrue($action->supports(new SyncRequest($modelMock)));
    }

    /**
     * @test
     */
    public function shouldNotSupportSyncRequestWithPaymentInstructionAggregateAsModelIfInstructionNotSet()
    {
        $modelMock = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue(null))
        ;

        $action = new SyncPaymentInstructionAggregateAction();

        $this->assertFalse($action->supports(new SyncRequest($modelMock)));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotSyncRequest()
    {
        $action = new SyncPaymentInstructionAggregateAction();
        
        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportSyncRequestAndNotPaymentInstructionAggregateAsModel()
    {
        $action = new SyncPaymentInstructionAggregateAction();
        
        $request = new SyncRequest(new \stdClass());
        
        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new SyncPaymentInstructionAggregateAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallPaymentExecuteWithSyncRequestAndInstructionSetAsModel()
    {
        $expectedInstruction = new \stdClass;
        
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Request\SyncRequest'))
            ->will($this->returnCallback(function($request) use ($expectedInstruction) {
                $this->assertSame($expectedInstruction, $request->getModel());
            }))
        ;
        
        $action = new SyncPaymentInstructionAggregateAction();
        $action->setPayment($paymentMock);

        $modelMock = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue($expectedInstruction))
        ;
        
        $action->execute(new SyncRequest($modelMock));
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}
