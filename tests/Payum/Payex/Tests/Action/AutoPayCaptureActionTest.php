<?php
namespace Payum\Payex\Tests\Action;

use Payum\PaymentInterface;
use Payum\Request\CaptureRequest;
use Payum\Payex\Action\AutoPayCaptureAction;
use Payum\Payex\Model\PaymentDetails;

class AutoPayCaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\AutoPayCaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AutoPayCaptureAction;
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithArrayAccessAsModelIfAutoPaySetToTrue()
    {
        $action = new AutoPayCaptureAction();

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

        $this->assertTrue($action->supports(new CaptureRequest($array)));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithArrayAccessAsModelIfAutoPayNotSet()
    {
        $action = new AutoPayCaptureAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('autoPay')
            ->will($this->returnValue(false))
        ;

        $this->assertFalse($action->supports(new CaptureRequest($array)));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithArrayAccessAsModelIfAutoPaySetToFalse()
    {
        $action = new AutoPayCaptureAction();

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

        $this->assertFalse($action->supports(new CaptureRequest($array)));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithPaymentDetailsAsModelIfAutoPaySetToTrue()
    {
        $action = new AutoPayCaptureAction;

        $details = new PaymentDetails;
        $details->setAutoPay(true);
        
        $this->assertTrue($action->supports(new CaptureRequest($details)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCaptureRequest()
    {
        $action = new AutoPayCaptureAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithNotArrayAccessModel()
    {
        $action = new AutoPayCaptureAction;

        $this->assertFalse($action->supports(new CaptureRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new AutoPayCaptureAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoSubExecuteAutoPayAgreementApiRequest()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\AutoPayAgreementRequest'))
        ;

        $action = new AutoPayCaptureAction();
        $action->setPayment($paymentMock);

        $request = new CaptureRequest(array(
            'autoPay' => true
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