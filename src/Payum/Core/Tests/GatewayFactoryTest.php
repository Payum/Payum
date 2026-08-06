<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use Payum\Core\Gateway;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use ReflectionClass;

final class GatewayFactoryTest extends TestCase
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
        $this->assertNotEmpty($config);
    }
}
