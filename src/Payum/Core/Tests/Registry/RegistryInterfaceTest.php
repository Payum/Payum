<?php
namespace Payum\Core\Tests\Registry;

class RegistryInterfaceTest extends \PHPUnit_Framework_TestCase
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
