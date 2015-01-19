<?php
namespace Payum\Core\Tests\Registry;

class RegistryInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\PaymentRegistryInterface'));
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
    public function shouldImplementPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\PaymentFactoryRegistryInterface'));
    }
}
