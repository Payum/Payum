<?php
namespace Payum\Payex\Tests\Action;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\CaptureRequest;
use Payum\Payex\Action\PaymentDetailsCaptureAction;

class PaymentDetailsCaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\PaymentDetailsCaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
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
    public function shouldSupportCaptureRequestWithArrayAsModelIfAutoPayNotSet()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new CaptureRequest(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithArrayAsModelIfAutoPaySet()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new CaptureRequest(array(
            'autoPay' => true
        ))));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithArrayAsModelIfAutoPaySetToFalse()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new CaptureRequest(array(
            'autoPay' => false
        ))));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithArrayAsModelIfRecurringSetToTrueAndAutoPaySet()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new CaptureRequest(array(
            'autoPay' => true,
            'recurring' => true
        ))));
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
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
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
    public function shouldDoSubExecuteCompleteOrderApiRequestIfOrderRefSet()
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
     * @test
     */
    public function shouldDoSubExecuteStartRecurringPaymentApiRequestIfRecurringSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\StartRecurringPaymentRequest'))
        ;

        $action = new PaymentDetailsCaptureAction();
        $action->setPayment($paymentMock);

        $request = new CaptureRequest(array(
            'orderRef' => 'aRef',
            'recurring' => true
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