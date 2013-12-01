<?php
namespace Payum\Payex\Tests\Action;

use Payum\PaymentInterface;
use Payum\Request\CaptureRequest;
use Payum\Payex\Action\AutoPayPaymentDetailsCaptureAction;

class AutoPayPaymentDetailsCaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\AutoPayPaymentDetailsCaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AutoPayPaymentDetailsCaptureAction;
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithArrayAsModelIfAutoPaySetToTrue()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new CaptureRequest(array(
            'autoPay' => true
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithArrayAsModelIfAutoPayNotSet()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new CaptureRequest(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithArrayAsModelIfAutoPaySetToTrueAndRecurringSetToTrue()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new CaptureRequest(array(
            'autoPay' => true,
            'recurring' => true,
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithArrayAsModelIfAutoPaySetToFalse()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new CaptureRequest(array(
            'autoPay' => false
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCaptureRequest()
    {
        $action = new AutoPayPaymentDetailsCaptureAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithNotArrayAccessModel()
    {
        $action = new AutoPayPaymentDetailsCaptureAction;

        $this->assertFalse($action->supports(new CaptureRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new AutoPayPaymentDetailsCaptureAction;

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

        $action = new AutoPayPaymentDetailsCaptureAction();
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