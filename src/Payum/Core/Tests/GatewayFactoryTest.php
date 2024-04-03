<?php

namespace Payum\Core\Tests;

use Payum\Core\Gateway;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use ReflectionClass;

class GatewayFactoryTest extends TestCase
{
    public function testShouldImplementGatewayFactoryInterface(): void
    {
        $rc = new ReflectionClass(GatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryInterface::class));
    }

    public function testShouldAllowCreateGateway(): void
    {
        $factory = new GatewayFactory();

        $gateway = $factory->create([]);

        $this->assertInstanceOf(Gateway::class, $gateway);
    }

    public function testShouldAllowCreateGatewayConfig(): void
    {
        $factory = new GatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }
}
