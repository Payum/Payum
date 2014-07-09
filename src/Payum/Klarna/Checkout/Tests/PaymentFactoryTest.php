<?php
namespace Payum\Klarna\Checkout\Tests;

use Payum\Klarna\Checkout\PaymentFactory;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function mustNotBeInstantiated()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\PaymentFactory');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAdded()
    {
        $connectorMock = $this->createConnectorMock();

        $payment = PaymentFactory::create($connectorMock);

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeCount(1, 'apis', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertAttributeCount(6, 'actions', $payment);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAndCustomRenderTemplateAction()
    {
        $connectorMock = $this->createConnectorMock();

        $payment = PaymentFactory::create($connectorMock, $this->getMock('Payum\Core\Action\ActionInterface'), 'aLayout', 'aTemplate');

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeCount(1, 'apis', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertAttributeCount(6, 'actions', $payment);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->getMock('Klarna_Checkout_ConnectorInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Twig_Environment
     */
    protected function createTwigMock()
    {
        return $this->getMock('Twig_Environment', array('render'), array(), '', false);
    }
}