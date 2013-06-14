<?php
namespace Payum\Payex\Tests;

use Payum\Payex\PaymentFactory;
use Payum\Payex\Api\OrderApi;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldNotBeInstantiated()
    {
        $rc = new \ReflectionClass('Payum\Payex\PaymentFactory');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAdded()
    {
        $orderApiMock = $this->createOrderApiMock();

        $payment = PaymentFactory::create($orderApiMock);

        $this->assertInstanceOf('Payum\Payment', $payment);

        $this->assertAttributeCount(1, 'apis', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertNotEmpty($actions);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\OrderApi
     */
    protected function createOrderApiMock()
    {
        return $this->getMock('Payum\Payex\Api\OrderApi', array(), array(), '', false);
    }
}