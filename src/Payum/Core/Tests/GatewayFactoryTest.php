<?php
namespace Payum\Core\Tests;

use Payum\Core\GatewayFactory;

class GatewayFactoryTest extends TestCase
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
    public function shouldAllowCreateGateway()
    {
        $factory = new GatewayFactory();

        $gateway = $factory->create(array());

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new GatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }
}
