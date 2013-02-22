<?php
namespace Payum\OmnipayBridge\Tests;

use Omnipay\Common\GatewayInterface;
use Payum\OmnipayBridge\Payment;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubclassOfPayumPayment()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\Payment');

        $this->assertTrue($rc->isSubclassOf('Payum\Payment'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithGatewayAsFirstArgument()
    {
        $gatewayMock = $this->createGatewayMock();

        $payment = new Payment($gatewayMock);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertEmpty($actions);

        $this->assertAttributeSame($gatewayMock, 'gateway', $payment);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAdded()
    {
        $gatewayMock = $this->createGatewayMock();
        
        $payment = Payment::create($gatewayMock);

        $this->assertAttributeSame($gatewayMock, 'gateway', $payment);

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