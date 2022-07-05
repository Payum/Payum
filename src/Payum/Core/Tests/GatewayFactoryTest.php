<?php

namespace Payum\Core\Tests;

use Payum\Core\GatewayFactory;

class GatewayFactoryTest extends TestCase
{
    public function testShouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\GatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\GatewayFactoryInterface::class));
    }

    public function testShouldAllowCreateGateway()
    {
        $factory = new GatewayFactory();

        $gateway = $factory->create([]);

        $this->assertInstanceOf(\Payum\Core\Gateway::class, $gateway);
    }

    public function testShouldAllowCreateGatewayConfig()
    {
        $factory = new GatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }
}
