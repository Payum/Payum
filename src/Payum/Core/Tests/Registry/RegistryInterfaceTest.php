<?php
namespace Payum\Core\Tests\Registry;

use PHPUnit\Framework\TestCase;

class RegistryInterfaceTest extends TestCase
{
    public function testShouldImplementGatewayRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\GatewayRegistryInterface'));
    }

    public function testShouldImplementStorageRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\StorageRegistryInterface'));
    }

    public function testShouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\GatewayFactoryRegistryInterface'));
    }
}
