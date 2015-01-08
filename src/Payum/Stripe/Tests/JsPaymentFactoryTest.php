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
    public function shouldCreateCorePaymentFactoryIfNotPassed()
    {
        $factory = new JsPaymentFactory();

        $this->assertAttributeInstanceOf('Payum\Core\PaymentFactory', 'corePaymentFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldUseCorePaymentFactoryPassedAsSecondArgument()
    {
        $corePaymentFactory = $this->getMock('Payum\Core\PaymentFactoryInterface');

        $factory = new JsPaymentFactory(array(), $corePaymentFactory);

        $this->assertAttributeSame($corePaymentFactory, 'corePaymentFactory', $factory);
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
     */
    public function shouldAllowCreatePaymentWithCustomApi()
    {
        $factory = new JsPaymentFactory();

        $payment = $factory->create(array('payum.api' => new \stdClass()));

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeNotEmpty('apis', $payment);
        $this->assertAttributeNotEmpty('actions', $payment);

        $extensions = $this->readAttribute($payment, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentConfig()
    {
        $factory = new JsPaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingPaymentConfig()
    {
        $factory = new JsPaymentFactory(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertEquals('barVal', $config['bar']);
    }

    /**
     * @test
     */
    public function shouldConfigContainDefaultOptions()
    {
        $factory = new JsPaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(array('publishable_key' => '', 'secret_key' => ''), $config['payum.default_options']);
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new JsPaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('stripe_js', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Stripe.Js', $config['payum.factory_title']);
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
