<?php
namespace Payum\Core\Tests\Registry;

class RegistryInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubInterfaceOfPaymentRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\PaymentRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubInterfaceOfStorageRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\StorageRegistryInterface'));
    }
}
