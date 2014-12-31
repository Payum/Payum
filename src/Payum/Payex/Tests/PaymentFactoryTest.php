<?php
namespace Payum\Payex\Tests;

use Payum\Payex\PaymentFactory;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\PaymentFactory');

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

        $payment = $factory->create(array('accountNumber' => 'aNum', 'encryptionKey' => 'aKey'));

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

        $payment = $factory->create(array(
            'payum.api.order' => new \stdClass(),
            'payum.api.agreement' => new \stdClass(),
            'payum.api.recurring' => new \stdClass()
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
        $this->assertEquals(array('accountNumber' => '', 'encryptionKey' => '', 'sandbox' => true), $config['options.default']);
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
        $this->assertEquals('payex', $config['factory.name']);

        $this->assertArrayHasKey('factory.title', $config);
        $this->assertEquals('Payex', $config['factory.title']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The accountNumber, encryptionKey fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new PaymentFactory();

        $factory->create();
    }
}
