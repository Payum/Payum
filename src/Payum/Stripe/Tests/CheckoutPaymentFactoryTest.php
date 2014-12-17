<?php
namespace Payum\Stripe\Tests;

use Payum\Stripe\CheckoutPaymentFactory;

class CheckoutPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementCheckoutPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Stripe\CheckoutPaymentFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\PaymentFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CheckoutPaymentFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreatePayment()
    {
        $factory = new CheckoutPaymentFactory();

        $payment = $factory->create(array('publishable_key' => 'aPubKey', 'secret_key' => 'aSecretKey'));

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeNotEmpty('apis', $payment);
        $this->assertAttributeNotEmpty('actions', $payment);

        $extensions = $this->readAttribute($payment, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The publishable_key, secret_key fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new CheckoutPaymentFactory();

        $factory->create();
    }
}