<?php
namespace Payum\Be2Bill\Tests;

use Payum\Be2Bill\OffsitePaymentFactory;

class OffsitePaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\OffsitePaymentFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\PaymentFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OffsitePaymentFactory();
    }

    /**
     * @test
     */
    public function shouldCreateCorePaymentFactoryIfNotPassed()
    {
        $factory = new OffsitePaymentFactory();

        $this->assertAttributeInstanceOf('Payum\Core\PaymentFactory', 'corePaymentFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldUseCorePaymentFactoryPassedAsSecondArgument()
    {
        $corePaymentFactory = $this->getMock('Payum\Core\PaymentFactoryInterface');

        $factory = new OffsitePaymentFactory(array(), $corePaymentFactory);

        $this->assertAttributeSame($corePaymentFactory, 'corePaymentFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePayment()
    {
        $factory = new OffsitePaymentFactory();

        $payment = $factory->create(array('identifier' => 'anId', 'password' => 'aPass'));

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
        $factory = new OffsitePaymentFactory();

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
        $factory = new OffsitePaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingPaymentConfig()
    {
        $factory = new OffsitePaymentFactory(array(
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
        $factory = new OffsitePaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(array('identifier' => '', 'password' => '', 'sandbox' => true), $config['payum.default_options']);
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new OffsitePaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('be2bill_offsite', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Be2Bill Offsite', $config['payum.factory_title']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The identifier, password fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new OffsitePaymentFactory();

        $factory->create();
    }
}
