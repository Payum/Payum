<?php
namespace Payum\OmnipayBridge\Tests;

use Omnipay\Common\GatewayInterface;
use Payum\OmnipayBridge\OnsitePaymentFactory;

class OnsitePaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldNotBeInstantiated()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\OnsitePaymentFactory');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAdded()
    {
        $gatewayMock = $this->createGatewayMock();
        
        $payment = OnsitePaymentFactory::create($gatewayMock);

        $this->assertInstanceOf('Payum\Core\Payment', $payment);
        
        $this->assertAttributeCount(1, 'apis', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertNotEmpty($actions);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Omnipay\Common\GatewayInterface');
    }
}