<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests;

use Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory;

class PaypalExpressCheckoutGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaypalExpressCheckoutGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldCreateCoreGatewayFactoryIfNotPassed()
    {
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $this->assertAttributeInstanceOf('Payum\Core\GatewayFactory', 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldUseCoreGatewayFactoryPassedAsSecondArgument()
    {
        $coreGatewayFactory = $this->getMock('Payum\Core\GatewayFactoryInterface');

        $factory = new PaypalExpressCheckoutGatewayFactory(array(), $coreGatewayFactory);

        $this->assertAttributeSame($coreGatewayFactory, 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $gateway = $factory->create(array(
            'username' => 'aName',
            'password' => 'aPass',
            'signature' => 'aSign',
        ));

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithCustomApi()
    {
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $gateway = $factory->create(array('payum.api' => new \stdClass()));

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new PaypalExpressCheckoutGatewayFactory(array(
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
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(
            array('username' => '', 'password' => '', 'signature' => '', 'sandbox' => true),
            $config['payum.default_options']
        );
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('paypal_express_checkout_nvp', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('PayPal ExpressCheckout', $config['payum.factory_title']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The username, password, signature fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new PaypalExpressCheckoutGatewayFactory();

        $factory->create();
    }
}
