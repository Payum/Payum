<?php
namespace Payum\Tests\Action;

use Payum\Action\StatusPaymentInstructionAggregateAction;
use Payum\Request\BinaryMaskStatusRequest;

class StatusPaymentInstructionAggregateActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Action\StatusPaymentInstructionAggregateAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new StatusPaymentInstructionAggregateAction();
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithPaymentInstructionAggregateAsModel()
    {
        $modelMock = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue(new \stdClass))
        ;
        
        $action = new StatusPaymentInstructionAggregateAction();

        $this->assertTrue($action->supports(new BinaryMaskStatusRequest($modelMock)));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithPaymentInstructionAggregateAsModelIfInstructionNotSet()
    {
        $modelMock = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue(null))
        ;

        $action = new StatusPaymentInstructionAggregateAction();

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest($modelMock)));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotStatusRequest()
    {
        $action = new StatusPaymentInstructionAggregateAction();
        
        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestAndNotPaymentInstructionAggregateAsModel()
    {
        $action = new StatusPaymentInstructionAggregateAction();
        
        $request = new BinaryMaskStatusRequest(new \stdClass());
        
        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new StatusPaymentInstructionAggregateAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallPaymentExecuteWithStatusRequestAndInstructionSetAsModel()
    {
        $expectedInstruction = new \stdClass;
        
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo('Payum\Request\BinaryMaskStatusRequest'))
            ->will($this->returnCallback(function($request) use ($expectedInstruction) {
                $this->assertSame($expectedInstruction, $request->getModel());
            }))
        ;
        
        $action = new StatusPaymentInstructionAggregateAction();
        $action->setPayment($paymentMock);

        $modelMock = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue($expectedInstruction))
        ;

        $request = new BinaryMaskStatusRequest($modelMock);
        $action->execute($request);
        
        $this->assertSame($modelMock, $request->getModel());
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}
