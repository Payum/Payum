<?php
namespace Payum\Core\Tests\Registry;

use PHPUnit\Framework\TestCase;

class RegistryInterfaceTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\GatewayRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementStorageRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\StorageRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\GatewayFactoryRegistryInterface'));
    }
}
