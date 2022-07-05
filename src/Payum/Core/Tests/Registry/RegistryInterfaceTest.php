<?php

namespace Payum\Core\Tests\Registry;

use PHPUnit\Framework\TestCase;

class RegistryInterfaceTest extends TestCase
{
    public function testShouldImplementGatewayRegistryInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Registry\RegistryInterface::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Registry\GatewayRegistryInterface::class));
    }

    public function testShouldImplementStorageRegistryInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Registry\RegistryInterface::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Registry\StorageRegistryInterface::class));
    }

    public function testShouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Registry\RegistryInterface::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Registry\GatewayFactoryRegistryInterface::class));
    }
}
