<?php
namespace Payum\Tests\Registry;

class RegistryInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubInterfaceOfPaymentRegistryInterface()
    { 
        $rc = new \ReflectionClass('Payum\Registry\RegistryInterface');
            
        $this->assertTrue($rc->isSubclassOf('Payum\Registry\PaymentRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubInterfaceOfStorageRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Registry\RegistryInterface');

        $this->assertTrue($rc->isSubclassOf('Payum\Registry\StorageRegistryInterface'));
    }
}