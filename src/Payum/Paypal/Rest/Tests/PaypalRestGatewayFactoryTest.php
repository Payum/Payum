<?php

namespace Payum\Paypal\Rest\Tests;

use Payum\Core\CoreGatewayFactory;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\Paypal\Rest\PaypalRestGatewayFactory;

class PaypalRestGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldSubClassGatewayFactory()
    {
        $rc = new \ReflectionClass(PaypalRestGatewayFactory::class);

        $this->assertTrue($rc->isSubclassOf(GatewayFactory::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaypalRestGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldCreateCoreGatewayFactoryIfNotPassed()
    {
        $factory = new PaypalRestGatewayFactory();

        $this->assertAttributeInstanceOf(CoreGatewayFactory::class, 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldUseCoreGatewayFactoryPassedAsSecondArgument()
    {
        $coreGatewayFactory = $this->getMock(GatewayFactoryInterface::class);

        $factory = new PaypalRestGatewayFactory([], $coreGatewayFactory);

        $this->assertAttributeSame($coreGatewayFactory, 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $factory = new PaypalRestGatewayFactory();

        $gateway = $factory->create([
            'client_id' => 'cId',
            'client_secret' => 'cSecret',
            'config_path' => __DIR__,
        ]);

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
        $factory = new PaypalRestGatewayFactory();

        $gateway = $factory->create(['payum.api' => new \stdClass()]);

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
        $factory = new PaypalRestGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new PaypalRestGatewayFactory([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

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
        $factory = new PaypalRestGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(['client_id' => '', 'client_secret' => '', 'config_path' => ''], $config['payum.default_options']);
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new PaypalRestGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('paypal_rest', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('PayPal Rest', $config['payum.factory_title']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The client_id, client_secret, config_path fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new PaypalRestGatewayFactory();

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
        $factory = new PaypalRestGatewayFactory();
        $factory->create([
            'client_id' => 'cId',
            'client_secret' => 'cSecret',
            'config_path' => dirname(__DIR__),
        ]);
    }
}
