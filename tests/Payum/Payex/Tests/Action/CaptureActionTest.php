<?php
namespace Payum\Payex\Tests\Action;

use Payum\PaymentInterface;
use Payum\Request\CaptureRequest;
use Payum\Payex\Action\CaptureAction;
use Payum\Payex\Model\PaymentDetails;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\CaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureAction;
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithArrayAccessAsModel()
    {
        $action = new CaptureAction();

        $this->assertTrue($action->supports(new CaptureRequest($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithPaymentDetailsAsModel()
    {
        $action = new CaptureAction;
        
        $this->assertTrue($action->supports(new CaptureRequest(new PaymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCaptureRequest()
    {
        $action = new CaptureAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithNotArrayAccessModel()
    {
        $action = new CaptureAction;

        $this->assertFalse($action->supports(new CaptureRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CaptureAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoSubExecuteInitializeOrderApiRequestIfOrderRefNotSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\InitializeOrderRequest'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\CompleteOrderRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $request = new CaptureRequest(array());
        
        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldNotDoSubExecuteInitializeOrderApiRequestIfOrderRefSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\CompleteOrderRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $request = new CaptureRequest(array(
            'orderRef' => 'aRef',
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}