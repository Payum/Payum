<?php
namespace Payum\AuthorizeNet\Aim\Tests;

use Payum\AuthorizeNet\Aim\PaymentFactory;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\PaymentFactory');

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

        $payment = $factory->create(array('loginId' => 'aLoginId', 'transactionKey' => 'aTransKey'));

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
        $this->assertEquals(array('loginId' => '', 'transactionKey' => '', 'sandbox' => true), $config['options.default']);
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
        $this->assertEquals('authorize_net_aim', $config['factory.name']);

        $this->assertArrayHasKey('factory.title', $config);
        $this->assertEquals('Authorize.NET AIM', $config['factory.title']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The loginId, transactionKey fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new PaymentFactory();

        $factory->create();
    }
}
