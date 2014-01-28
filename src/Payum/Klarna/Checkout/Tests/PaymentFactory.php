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
        $payment = PaymentFactory::create();

        $this->assertInstanceOf('Payum\Core\Payment', $payment);
    }
}