<?php
namespace Payum\Tests\Action;

use Payum\Action\CaptureDetailsAggregatedModelAction;
use Payum\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Request\CaptureRequest;

class ExecuteSameRequestWithModelDetailsActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Action\ExecuteSameRequestWithModelDetailsAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new ExecuteSameRequestWithModelDetailsAction;
    }

    /**
     * @test
     */
    public function shouldSupportModelRequestWithModelThatAggregateNotEmptyDetails()
    {
        $modelMock = $this->getMock('Payum\Model\DetailsAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getDetails')
            ->will($this->returnValue(new \stdClass))
        ;

        $requestMock = $this->getMock('Payum\Request\ModelRequestInterface');
        $requestMock
            ->expects($this->atLeastOnce())
            ->method('getModel')
            ->will($this->returnValue($modelMock))
        ;

        $action = new ExecuteSameRequestWithModelDetailsAction;

        $this->assertTrue($action->supports($requestMock));
    }

    /**
     * @test
     */
    public function shouldNotSupportModelRequestWithModelThatAggregateEmptyDetails()
    {
        $modelMock = $this->getMock('Payum\Model\DetailsAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getDetails')
            ->will($this->returnValue(null))
        ;

        $requestMock = $this->getMock('Payum\Request\ModelRequestInterface');
        $requestMock
            ->expects($this->atLeastOnce())
            ->method('getModel')
            ->will($this->returnValue($modelMock))
        ;

        $action = new ExecuteSameRequestWithModelDetailsAction;

        $this->assertFalse($action->supports($requestMock));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotModelRequest()
    {
        $action = new ExecuteSameRequestWithModelDetailsAction();
        
        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportModelRequestWithModelThatNotAggregateDetails()
    {
        $action = new ExecuteSameRequestWithModelDetailsAction();
        
        $requestMock = $this->getMock('Payum\Request\ModelRequestInterface');
        $requestMock
            ->expects($this->atLeastOnce())
            ->method('getModel')
            ->will($this->returnValue(new \stdClass))
        ;
        
        $this->assertFalse($action->supports($requestMock));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new ExecuteSameRequestWithModelDetailsAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldExecuteSameRequestWithModelDetails()
    {
        $expectedDetails = new \stdClass;

        $modelMock = $this->getMock('Payum\Model\DetailsAggregateInterface');
        $modelMock
            ->expects($this->atLeastOnce())
            ->method('getDetails')
            ->will($this->returnValue($expectedDetails))
        ;

        $request = new CaptureRequest($modelMock);

        //guard
        $this->assertInstanceOf('Payum\Request\ModelRequestInterface', $request);

        $testCase = $this;
        
        $paymentMock = $this->getMock('Payum\PaymentInterface');
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->identicalTo($request))
            ->will($this->returnCallback(function($request) use ($expectedDetails, $testCase) {
                $testCase->assertSame($expectedDetails, $request->getModel());
            }))
        ;
        
        $action = new ExecuteSameRequestWithModelDetailsAction;
        $action->setPayment($paymentMock);

        $action->execute($request);
    }
}
