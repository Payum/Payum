<?php
namespace Payum\Stripe\Tests;

use Payum\Stripe\Keys;
use Payum\Stripe\PaymentFactory;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldNotBeInstantiated()
    {
        $rc = new \ReflectionClass('Payum\Stripe\PaymentFactory');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldAllowCreateJsPaymentWithKeysAddedAsApi()
    {
        $keys = new Keys('', '');

        $payment = PaymentFactory::createJs($keys);

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeCount(1, 'apis', $payment);

        $this->assertAttributeContains($keys, 'apis', $payment);
    }

    /**
     * @test
     */
    public function shouldAllowCreateJsPaymentWithExpectedNumberOfActions()
    {
        $keys = new Keys('', '');

        $payment = PaymentFactory::createJs($keys);

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeCount(8, 'actions', $payment);
    }

    /**
     * @test
     */
    public function shouldAllowCreateCheckoutPaymentWithKeysAddedAsApi()
    {
        $keys = new Keys('', '');

        $payment = PaymentFactory::createCheckout($keys);

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeCount(1, 'apis', $payment);

        $this->assertAttributeContains($keys, 'apis', $payment);
    }

    /**
     * @test
     */
    public function shouldAllowCreateCheckoutPaymentWithExpectedNumberOfActions()
    {
        $keys = new Keys('', '');

        $payment = PaymentFactory::createCheckout($keys);

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeCount(8, 'actions', $payment);
    }
}