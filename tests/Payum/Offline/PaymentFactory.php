<?php
namespace Payum\Offline\Tests;

use Payum\Offline\PaymentFactory;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function mustNotBeInstantiated()
    {
        $rc = new \ReflectionClass('Payum\Offline\PaymentFactory');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAdded()
    {
        $payment = PaymentFactory::create();

        $this->assertInstanceOf('Payum\Payment', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertNotEmpty($actions);
    }
}