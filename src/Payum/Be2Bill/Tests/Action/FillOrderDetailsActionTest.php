<?php
namespace Payum\Be2bill\Tests\Action\Api;

use Payum\Be2Bill\Action\FillOrderDetailsAction;
use Payum\Core\Model\Order;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Tests\BaseFillOrderDetailsActionTest;

class FillOrderDetailsActionTest extends BaseFillOrderDetailsActionTest
{
    protected $actionClass = 'Payum\Be2Bill\Action\FillOrderDetailsAction';

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $order = new Order;
        $order->setNumber('theNumber');
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setClientId('theClientId');
        $order->setClientEmail('theClientEmail');

        $action = new FillOrderDetailsAction;

        $action->execute(new FillOrderDetails($order));

        $details = $order->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('AMOUNT', $details);
        $this->assertEquals(123, $details['AMOUNT']);

        $this->assertArrayHasKey('ORDERID', $details);
        $this->assertEquals('theNumber', $details['ORDERID']);

        $this->assertArrayHasKey('DESCRIPTION', $details);
        $this->assertEquals('the description', $details['DESCRIPTION']);

        $this->assertArrayHasKey('CLIENTIDENT', $details);
        $this->assertEquals('theClientId', $details['CLIENTIDENT']);

        $this->assertArrayHasKey('CLIENTEMAIL', $details);
        $this->assertEquals('theClientEmail', $details['CLIENTEMAIL']);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $order = new Order;
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new FillOrderDetailsAction;

        $action->execute(new FillOrderDetails($order));

        $details = $order->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }
}