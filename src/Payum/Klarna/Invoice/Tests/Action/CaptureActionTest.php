<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Klarna\Invoice\Action\CaptureAction;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\CaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
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
    public function shouldSupportCaptureWithArrayAsModel()
    {
        $action = new CaptureAction();

        $this->assertTrue($action->supports(new Capture(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCapture()
    {
        $action = new CaptureAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureWithNotArrayAccessModel()
    {
        $action = new CaptureAction;

        $this->assertFalse($action->supports(new Capture(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new CaptureAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSubExecuteAuthorizeIfRnoNotSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Authorize'))
        ;

        $action = new CaptureAction;
        $action->setPayment($paymentMock);

        $request = new Capture(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldSubExecuteActivateIfRnoSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\Activate'))
        ;

        $action = new CaptureAction;
        $action->setPayment($paymentMock);

        $request = new Capture(array(
            'rno' => 'aRno',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfAlreadyReservedAndActivated()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction;
        $action->setPayment($paymentMock);

        $request = new Capture(array(
            'rno' => 'aRno',
            'invoice_number' => 'anInvNumber'
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}