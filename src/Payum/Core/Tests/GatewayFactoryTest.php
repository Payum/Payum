<?php
namespace Payum\Core\Tests;

use Payum\Core\GatewayFactory;

class GatewayFactoryTest extends TestCase
{
    public function testShouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\GatewayFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayFactoryInterface'));
    }

    public function testShouldAllowCreateGateway()
    {
        $factory = new GatewayFactory();

        $gateway = $factory->create(array());

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);
    }

    public function testShouldAllowCreateGatewayConfig()
    {
        $factory = new GatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }
}
