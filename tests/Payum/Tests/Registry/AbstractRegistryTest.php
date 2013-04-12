<?php
namespace Payum\Tests\Registry;

use Payum\Registry\AbstractRegistry;

class AbstractRegistryTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     */
    public function shouldImplementPaymentRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Registry\PaymentRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementStorageRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Registry\StorageRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstractClass()
    {
        $rc = new \ReflectionClass('Payum\Registry\AbstractRegistry');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function couldConstructedWithPaymentsStoragesAndTheirDefaultNames()
    {
        $payments = array('fooName' => 'fooPayment');
        $storages = array('bar' => array('stdClass' => 'barStorage'));
        
        $paymentName = 'foo';
        $storageName = 'bar';
        
        $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultPaymentName()
    {
        $payments = array('fooName' => 'fooPayment');
        $storages = array('bar' => array('stdClass' => 'barStorage'));

        $paymentName = 'foo';
        $storageName = 'bar';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));

        $this->assertEquals($paymentName, $registry->getDefaultPaymentName());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultStorageName()
    {
        $payments = array('fooName' => 'fooPayment');
        $storages = array('bar' => array('stdClass' => 'barStorage'));

        $paymentName = 'foo';
        $storageName = 'bar';

        $registry = $this->createAbstractRegistryMock(array(
                $payments,
                $storages,
                $paymentName,
                $storageName
            ));

        $this->assertEquals($storageName, $registry->getDefaultStorageName());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultPayment()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('bar' => array('stdClass' => 'barStorage'));

        $paymentName = 'fooName';
        $storageName = 'bar';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));

        $this->assertEquals('fooPayment', $registry->getPayment());
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentWithNamePassedExplicitly()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('barName' => array('stdClass' => 'barStorage'));

        $paymentName = 'fooName';
        $storageName = 'barName';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));

        $this->assertEquals('barPayment', $registry->getPayment('barName'));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Payum payment named notExistName does not exist.
     */
    public function throwIfTryToGetPaymentWithNotExistName()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('bar' => array('stdClass' => 'barStorage'));

        $paymentName = 'fooName';
        $storageName = 'bar';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));

        $registry->getPayment('notExistName');
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultStorage()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('barName' => array('stdClass' => 'barStorage'));

        $paymentName = 'fooName';
        $storageName = 'barName';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));

        $this->assertEquals('barStorage', $registry->getStorageForClass('stdClass'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid class argument given. Must be string class or model instance.
     */
    public function throwIfTryToGetStorageWithNotExistModelClass()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('barName' => array('stdClass' => 'barStorage'));

        $paymentName = 'fooName';
        $storageName = 'barName';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));

        $this->assertEquals('barStorage', $registry->getStorageForClass('notExistModelClass'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Payum storage named notExistName for class "stdClass" does not exist.
     */
    public function throwIfTryToGetStorageWithNotExistName()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('barName' => array('stdClass' => 'barStorage'));

        $paymentName = 'fooName';
        $storageName = 'barName';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));
        
        $this->assertEquals('barStorage', $registry->getStorageForClass('stdClass', 'notExistName'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageWithNamePassedExplicitly()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('barName' => array('stdClass' => 'barStorage'));

        $paymentName = 'fooName';
        $storageName = 'barName';

        $registry = $this->createAbstractRegistryMock(array(
                $payments,
                $storages,
                $paymentName,
                $storageName
            ));

        $this->assertEquals('barStorage', $registry->getStorageForClass('stdClass', 'barName'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageWithObjectModel()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('barName' => array('stdClass' => 'barStorage'));

        $paymentName = 'fooName';
        $storageName = 'barName';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));

        $this->assertEquals('barStorage', $registry->getStorageForClass(new \stdClass, 'barName'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractRegistry
     */
    protected function createAbstractRegistryMock(array $constructorArguments)
    {
        $registryMock = $this->getMockForAbstractClass('Payum\Registry\AbstractRegistry', $constructorArguments);
        
        $registryMock
            ->expects($this->any())
            ->method('getService')
            ->will($this->returnArgument(0))
        ;
        
        return $registryMock;
    }
}