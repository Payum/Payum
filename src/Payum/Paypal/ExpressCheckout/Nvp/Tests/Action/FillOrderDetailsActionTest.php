<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\FillOrderDetailsAction;
use Payum\Core\Model\Order;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Tests\BaseFillOrderDetailsActionTest;

class FillOrderDetailsActionTest extends BaseFillOrderDetailsActionTest
{
    protected $actionClass = 'Payum\Paypal\ExpressCheckout\Nvp\Action\FillOrderDetailsAction';

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

        $this->assertArrayHasKey('INVNUM', $details);
        $this->assertEquals('theNumber', $details['INVNUM']);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $details);
        $this->assertEquals(1.23, $details['PAYMENTREQUEST_0_AMT']);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_CURRENCYCODE', $details);
        $this->assertEquals('USD', $details['PAYMENTREQUEST_0_CURRENCYCODE']);
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