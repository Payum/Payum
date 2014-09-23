<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\PaymentInterface;
use Payum\Klarna\Invoice\Action\Api\CreditPartAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CreditPart;

class CreditPartActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CreditPartAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function shouldImplementsPaymentAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CreditPartAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\PaymentAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreditPartAction;
    }

    /**
     * @test
     */
    public function couldBeConstructedWithKlarnaAsArgument()
    {
        new CreditPartAction($this->createKlarnaMock());
    }

    /**
     * @test
     */
    public function shouldAllowSetPayment()
    {
        $action = new CreditPartAction($this->createKlarnaMock());

        $action->setPayment($payment = $this->getMock('Payum\Core\PaymentInterface'));

        $this->assertAttributeSame($payment, 'payment', $action);
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = new CreditPartAction($this->createKlarnaMock());

        $action->setApi($config = new Config);

        $this->assertAttributeSame($config, 'config', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     * @expectedExceptionMessage Instance of Config is expected to be passed as api.
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $action = new CreditPartAction($this->createKlarnaMock());

        $action->setApi(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldSupportCreditPartWithArrayAsModel()
    {
        $action = new CreditPartAction;

        $this->assertTrue($action->supports(new CreditPart(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCreditPart()
    {
        $action = new CreditPartAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCreditPartWithNotArrayAccessModel()
    {
        $action = new CreditPartAction;

        $this->assertFalse($action->supports(new CreditPart(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new CreditPartAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The invoice_number fields is required.
     */
    public function throwIfDetailsDoNotHaveInvoiceNumber()
    {
        $action = new CreditPartAction;

        $action->execute(new CreditPart(array()));
    }

    /**
     * @test
     */
    public function shouldCallKlarnaCreditPart()
    {
        $details = array(
            'invoice_number' => 'theInvNum',
        );

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails'))
        ;

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('creditPart')
            ->with($details['invoice_number'])
            ->will($this->returnValue('theRefundInvoiceNumber'))
        ;

        $action = new CreditPartAction($klarnaMock);
        $action->setApi(new Config);
        $action->setPayment($paymentMock);

        $action->execute($creditPart = new CreditPart($details));

        $actualDetails = $creditPart->getModel();
        $this->assertContains('theRefundInvoiceNumber', $actualDetails['refund_invoice_number']);
    }

    /**
     * @test
     */
    public function shouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = array(
            'invoice_number' => 'theInvNum',
        );

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails'))
        ;

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('creditPart')
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new CreditPartAction($klarnaMock);
        $action->setApi(new Config);
        $action->setPayment($paymentMock);

        $action->execute($creditPart = new CreditPart($details));

        $actualDetails = $creditPart->getModel();
        $this->assertEquals(123, $actualDetails['error_code']);
        $this->assertEquals('theMessage', $actualDetails['error_message']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->getMock('Klarna', array('config', 'activate', 'cancelReservation', 'checkOrderStatus', 'reserveAmount', 'creditPart'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->getMock('xmlrpc_client', array(), array(), '', false));
        $rp->setAccessible(false);

        return $klarnaMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}