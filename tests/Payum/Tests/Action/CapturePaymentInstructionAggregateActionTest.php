<?php
namespace Payum\Tests\Action;

use Payum\Action\CapturePaymentInstructionAggregateAction;
use Payum\Request\CaptureRequest;

class CapturePaymentInstructionAggregateActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Action\CapturePaymentInstructionAggregateAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new CapturePaymentInstructionAggregateAction();
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithPaymentInstructionAggregateAsModel()
    {
        $modelMock = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue(new \stdClass))
        ;
        
        $action = new CapturePaymentInstructionAggregateAction();

        $this->assertTrue($action->supports(new CaptureRequest($modelMock)));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithPaymentInstructionAggregateAsModelIfInstructionNotSet()
    {
        $modelMock = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue(null))
        ;

        $action = new CapturePaymentInstructionAggregateAction();

        $this->assertFalse($action->supports(new CaptureRequest($modelMock)));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureRequest()
    {
        $action = new CapturePaymentInstructionAggregateAction();
        
        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestAndNotPaymentInstructionAggregateAsModel()
    {
        $action = new CapturePaymentInstructionAggregateAction();
        
        $request = new CaptureRequest(new \stdClass());
        
        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CapturePaymentInstructionAggregateAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallPaymentExecuteWithCaptureRequestAndInstructionSetAsModel()
    {
        $expectedInstruction = new \stdClass;
        
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Request\CaptureRequest'))
            ->will($this->returnCallback(function($request) use ($expectedInstruction) {
                $this->assertSame($expectedInstruction, $request->getModel());
            }))
        ;
        
        $action = new CapturePaymentInstructionAggregateAction();
        $action->setPayment($paymentMock);

        $modelMock = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue($expectedInstruction))
        ;
        
        $action->execute(new CaptureRequest($modelMock));
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}
