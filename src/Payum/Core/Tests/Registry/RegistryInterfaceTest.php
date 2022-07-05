<?php

namespace Payum\Core\Tests\Registry;

use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Payum\Core\Registry\GatewayRegistryInterface;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RegistryInterfaceTest extends TestCase
{
    public function testShouldImplementGatewayRegistryInterface()
    {
        $rc = new ReflectionClass(RegistryInterface::class);

        $this->assertTrue($rc->isSubclassOf(GatewayRegistryInterface::class));
    }

    public function testShouldImplementStorageRegistryInterface()
    {
        $rc = new ReflectionClass(RegistryInterface::class);

        $this->assertTrue($rc->isSubclassOf(StorageRegistryInterface::class));
    }

    public function testShouldImplementGatewayFactoryInterface()
    {
        $rc = new ReflectionClass(RegistryInterface::class);

        $this->assertTrue($rc->isSubclassOf(GatewayFactoryRegistryInterface::class));
    }
}
