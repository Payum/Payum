<?php
namespace Payum\Payex\Tests\Action;

use Payum\PaymentInterface;
use Payum\Request\CaptureRequest;
use Payum\Payex\Action\PaymentDetailsCaptureAction;
use Payum\Payex\Model\PaymentDetails;

class PaymentDetailsCaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\PaymentDetailsCaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentDetailsCaptureAction;
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithArrayAccessAsModelIfAutoPayNotSet()
    {
        $action = new PaymentDetailsCaptureAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('autoPay')
            ->will($this->returnValue(false))
        ;

        $this->assertTrue($action->supports(new CaptureRequest($array)));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithArrayAccessAsModelIfAutoPaySet()
    {
        $action = new PaymentDetailsCaptureAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('autoPay')
            ->will($this->returnValue(true))
        ;
        $array
            ->expects($this->once())
            ->method('offsetGet')
            ->with('autoPay')
            ->will($this->returnValue(true))
        ;

        $this->assertFalse($action->supports(new CaptureRequest($array)));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithArrayAccessAsModelIfAutoPaySetToFalse()
    {
        $action = new PaymentDetailsCaptureAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('autoPay')
            ->will($this->returnValue(true))
        ;
        $array
            ->expects($this->once())
            ->method('offsetGet')
            ->with('autoPay')
            ->will($this->returnValue(false))
        ;

        $this->assertTrue($action->supports(new CaptureRequest($array)));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithPaymentDetailsAsModel()
    {
        $action = new PaymentDetailsCaptureAction;
        
        $this->assertTrue($action->supports(new CaptureRequest(new PaymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCaptureRequest()
    {
        $action = new PaymentDetailsCaptureAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithNotArrayAccessModel()
    {
        $action = new PaymentDetailsCaptureAction;

        $this->assertFalse($action->supports(new CaptureRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new PaymentDetailsCaptureAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoSubExecuteInitializeOrderApiRequestIfOrderRefNotSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\InitializeOrderRequest'))
        ;

        $action = new PaymentDetailsCaptureAction();
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

        $action = new PaymentDetailsCaptureAction();
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