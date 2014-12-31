<?php

namespace Payum\Paypal\Rest\Tests;

use Payum\Paypal\Rest\PaymentFactory;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\Rest\PaymentFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\PaymentFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreatePayment()
    {
        $factory = new PaymentFactory();

        $payment = $factory->create(array(
            'client_id' => 'cId',
            'client_secret' => 'cSecret',
            'config_path' => __DIR__,
        ));

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
        $factory = new PaymentFactory();

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
        $factory = new PaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldConfigContainDefaultOptions()
    {
        $factory = new PaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('options.default', $config);
        $this->assertEquals(array('client_id' => '', 'client_secret' => '', 'config_path' => ''), $config['options.default']);
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new PaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('factory.name', $config);
        $this->assertEquals('paypal_rest', $config['factory.name']);

        $this->assertArrayHasKey('factory.title', $config);
        $this->assertEquals('PayPal Rest', $config['factory.title']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The client_id, client_secret, config_path fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new PaymentFactory();

        $factory->create();
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessageRegExp /Given \"config_path\" is invalid. \w+/
     */
    public function shouldThrowIfConfigPathOptionsNotEqualPaypalPath()
    {
        $factory = new PaymentFactory();
        $factory->create(array(
            'client_id' => 'cId',
            'client_secret' => 'cSecret',
            'config_path' => dirname(__DIR__),
        ));
    }
}
