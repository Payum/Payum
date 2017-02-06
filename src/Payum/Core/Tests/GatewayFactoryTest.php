<?php
namespace Payum\Core\Tests;

use Payum\Core\GatewayFactory;

class GatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\GatewayFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new GatewayFactory();
    }

    /**
     * @test
     */
    public function shouldCreateCoreGatewayFactoryIfNotPassed()
    {
        $factory = new GatewayFactory();

        $this->assertAttributeInstanceOf('Payum\Core\CoreGatewayFactory', 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldUseCoreGatewayFactoryPassedAsSecondArgument()
    {
        $coreGatewayFactory = $this->getMock('Payum\Core\CoreGatewayFactory');

        $factory = new GatewayFactory(array(), $coreGatewayFactory);

        $this->assertAttributeSame($coreGatewayFactory, 'coreGatewayFactory', $factory);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $factory = new GatewayFactory();

        $gateway = $factory->create(array());

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
        $factory = new GatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }
}
