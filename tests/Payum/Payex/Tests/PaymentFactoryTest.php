<?php
namespace Payum\Payex\Tests;

use Payum\Payex\PaymentFactory;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\AgreementApi;

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
        $this->assertCount(6, $actions);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAddedPlusAgreementOnes()
    {
        $orderApiMock = $this->createOrderApiMock();
        $agreementApiMock = $this->createAgreementApiMock();

        $payment = PaymentFactory::create($orderApiMock, $agreementApiMock);

        $this->assertInstanceOf('Payum\Payment', $payment);

        $this->assertAttributeCount(2, 'apis', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertCount(11, $actions);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\OrderApi
     */
    protected function createOrderApiMock()
    {
        return $this->getMock('Payum\Payex\Api\OrderApi', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AgreementApi
     */
    protected function createAgreementApiMock()
    {
        return $this->getMock('Payum\Payex\Api\AgreementApi', array(), array(), '', false);
    }
}