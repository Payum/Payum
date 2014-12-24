<?php
namespace Payum\AuthorizeNet\Aim\Tests\Action\Api;

use Payum\AuthorizeNet\Aim\Action\FillOrderDetailsAction;
use Payum\Core\Model\Order;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Tests\GenericActionTest;

class FillOrderDetailsActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\AuthorizeNet\Aim\Action\FillOrderDetailsAction';

    protected $requestClass = 'Payum\Core\Request\FillOrderDetails';

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Order)),
            array(new $this->requestClass($this->getMock('Payum\Core\Model\OrderInterface'))),
            array(new $this->requestClass(new Order, $this->getMock('Payum\Core\Security\TokenInterface'))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
        );
    }

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

        $this->assertArrayHasKey('amount', $details);
        $this->assertEquals(1.23, $details['amount']);

        $this->assertArrayHasKey('invoice_number', $details);
        $this->assertEquals('theNumber', $details['invoice_number']);

        $this->assertArrayHasKey('description', $details);
        $this->assertEquals('the description', $details['description']);

        $this->assertArrayHasKey('customer_id', $details);
        $this->assertEquals('theClientId', $details['customer_id']);

        $this->assertArrayHasKey('email_address', $details);
        $this->assertEquals('theClientEmail', $details['email_address']);
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
