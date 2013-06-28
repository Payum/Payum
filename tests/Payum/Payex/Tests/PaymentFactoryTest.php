<?php
namespace Payum\Payex\Tests;

use Payum\Payex\PaymentFactory;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\RecurringApi;
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
        $this->assertCount(8, $actions);
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
        $this->assertCount(14, $actions);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAddedPlusRecurringOnes()
    {
        $orderApiMock = $this->createOrderApiMock();
        $recurringApiMock = $this->createRecurringApiMock();
        
        $payment = PaymentFactory::create($orderApiMock, null, $recurringApiMock);

        $this->assertInstanceOf('Payum\Payment', $payment);

        $this->assertAttributeCount(2, 'apis', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertCount(11, $actions);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAddedPlusAgreementAndRecurringOnes()
    {
        $orderApiMock = $this->createOrderApiMock();
        $agreementApiMock = $this->createAgreementApiMock();
        $recurringApiMock = $this->createRecurringApiMock();

        $payment = PaymentFactory::create($orderApiMock, $agreementApiMock, $recurringApiMock);

        $this->assertInstanceOf('Payum\Payment', $payment);

        $this->assertAttributeCount(3, 'apis', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertCount(17, $actions);
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RecurringApi
     */
    protected function createRecurringApiMock()
    {
        return $this->getMock('Payum\Payex\Api\RecurringApi', array(), array(), '', false);
    }
}