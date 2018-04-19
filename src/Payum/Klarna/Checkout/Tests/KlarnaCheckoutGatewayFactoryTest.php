<?php
namespace Payum\Klarna\Checkout\Tests;

use Payum\Core\CoreGatewayFactory;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory;

class KlarnaCheckoutGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldSubClassGatewayFactory()
    {
        $rc = new \ReflectionClass(KlarnaCheckoutGatewayFactory::class);

        $this->assertTrue($rc->isSubclassOf(GatewayFactory::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new KlarnaCheckoutGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldCreateCoreGatewayFactoryIfNotPassed()
    {
        $factory = new KlarnaCheckoutGatewayFactory();

        $this->assertAttributeInstanceOf(CoreGatewayFactory::class, 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldUseCoreGatewayFactoryPassedAsSecondArgument()
    {
        $coreGatewayFactory = $this->getMock(GatewayFactoryInterface::class);

        $factory = new KlarnaCheckoutGatewayFactory(array(), $coreGatewayFactory);

        $this->assertAttributeSame($coreGatewayFactory, 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $factory = new KlarnaCheckoutGatewayFactory();

        $gateway = $factory->create(array('merchant_id' => 'aMerchId', 'secret' => 'aSecret'));

        $this->assertInstanceOf(Gateway::class, $gateway);

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
        $factory = new KlarnaCheckoutGatewayFactory();

        $gateway = $factory->create(array('payum.api' => new \stdClass()));

        $this->assertInstanceOf(Gateway::class, $gateway);

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
        $factory = new KlarnaCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new KlarnaCheckoutGatewayFactory(array(
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
        $factory = new KlarnaCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(
            array(
                'merchant_id' => '',
                'secret' => '',
                'checkout_uri' => '',
                'terms_uri' => '',
                'sandbox' => true,
            ),
            $config['payum.default_options']
        );
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new KlarnaCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('klarna_checkout', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Klarna Checkout', $config['payum.factory_title']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The merchant_id, secret fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new KlarnaCheckoutGatewayFactory();

        $factory->create();
    }

    /**
     * @test
     */
    public function shouldConfigurePaths()
    {
        $factory = new KlarnaCheckoutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);

        $this->assertInternalType('array', $config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertTrue(file_exists($config['payum.paths']['PayumCore']));

        $this->assertArrayHasKey('PayumKlarnaCheckout', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumKlarnaCheckout']);
        $this->assertTrue(file_exists($config['payum.paths']['PayumKlarnaCheckout']));
    }
}
