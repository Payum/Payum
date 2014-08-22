<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Klarna\Invoice\Action\Api\ReserveAmountAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\ReserveAmount;

class ReserveAmountActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\ReserveAmountAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function shouldImplementsPaymentAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\ReserveAmountAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\PaymentAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new ReserveAmountAction;
    }

    /**
     * @test
     */
    public function couldBeConstructedWithKlarnaAsArgument()
    {
        new ReserveAmountAction($this->createKlarnaMock());
    }

    /**
     * @test
     */
    public function shouldAllowSetPayment()
    {
        $action = new ReserveAmountAction($this->createKlarnaMock());

        $action->setPayment($payment = $this->getMock('Payum\Core\PaymentInterface'));

        $this->assertAttributeSame($payment, 'payment', $action);
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = new ReserveAmountAction($this->createKlarnaMock());

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
        $action = new ReserveAmountAction($this->createKlarnaMock());

        $action->setApi(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldSupportReserveAmountWithArrayAsModel()
    {
        $action = new ReserveAmountAction;

        $this->assertTrue($action->supports(new ReserveAmount(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotReserveAmount()
    {
        $action = new ReserveAmountAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportReserveAmountWithNotArrayAccessModel()
    {
        $action = new ReserveAmountAction;

        $this->assertFalse($action->supports(new ReserveAmount(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new ReserveAmountAction;

        $action->execute(new \stdClass());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->getMock('Klarna', array('config', 'activate', 'cancelReservation', 'checkOrderStatus'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->getMock('xmlrpc_client', array(), array(), '', false));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}