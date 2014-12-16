<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\Refund;
use Payum\Klarna\Invoice\Action\RefundAction;

class RefundActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\RefundAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RefundAction;
    }

    /**
     * @test
     */
    public function shouldSupportRefundWithArrayAsModel()
    {
        $action = new RefundAction();

        $this->assertTrue($action->supports(new Refund(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotRefund()
    {
        $action = new RefundAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportRefundWithNotArrayAccessModel()
    {
        $action = new RefundAction;

        $this->assertFalse($action->supports(new Refund(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new RefundAction;

        $action->execute(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldSubExecuteCreditPartIfNotRefundedYet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\CreditPart'))
        ;

        $action = new RefundAction;
        $action->setPayment($paymentMock);

        $request = new Refund(array(
            'invoice_number' => 'aNum'
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfAlreadyRefunded()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new RefundAction;
        $action->setPayment($paymentMock);

        $request = new Refund(array(
            'invoice_number' => 'aNum',
            'refund_invoice_number' => 'aFooNum',
        ));

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The invoice_number fields are required.
     */
    public function shouldThrowsIfDetailsNotHaveInvoiceNumber()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new RefundAction;
        $action->setPayment($paymentMock);

        $request = new Refund(array());

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