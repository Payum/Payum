<?php
namespace Payum\Stripe\Tests;

use Payum\Stripe\JsPaymentFactory;

class JsPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementJsPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Stripe\JsPaymentFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\PaymentFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new JsPaymentFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreatePayment()
    {
        $factory = new JsPaymentFactory();

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
        $factory = new JsPaymentFactory();

        $factory->create();
    }
}