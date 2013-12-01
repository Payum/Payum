<?php
namespace Payum\Tests\Registry;

use Payum\Core\Registry\AbstractRegistry;

class AbstractRegistryTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     */
    public function shouldImplementPaymentRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\PaymentRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementStorageRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\StorageRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstractClass()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

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
    public function couldConstructedWithPaymentsStoragesOnly()
    {
        $payments = array('fooName' => 'fooPayment');
        $storages = array('bar' => array('stdClass' => 'barStorage'));

        $this->createAbstractRegistryMock(array(
            $payments,
            $storages
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
    public function shouldAllowGetDefaultPaymentNameSetInConstructor()
    {
        $payments = array('fooName' => 'fooPayment');
        $storages = array('bar' => array('stdClass' => 'barStorage'));

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
        ));

        $this->assertEquals('default', $registry->getDefaultPaymentName());
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
    public function shouldAllowGetDefaultStorageNameSetInConstructor()
    {
        $payments = array('fooName' => 'fooPayment');
        $storages = array('bar' => array('stdClass' => 'barStorage'));

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
        ));

        $this->assertEquals('default', $registry->getDefaultStorageName());
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
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
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
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage A storage for payment barName and model notRegisteredModelClass was not registered. The payment supports storages for next models: stdClass.
     */
    public function throwIfTryToGetStorageWithNotRegisteredModelClass()
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

        $this->assertEquals('barStorage', $registry->getStorageForClass('notRegisteredModelClass'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Any storages for payment notExistName were not registered. Registered payments: barName, fooName.
     */
    public function throwIfTryToGetStorageForNotExistPayment()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array(
            'barName' => array(
                'stdClass' => 'barStorage'
            ),
            'fooName' => array(
                'stdClass' => 'fooStorage'
            )
        );

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
     * @test
     */
    public function shouldAllowGetStoragesForDefaultName()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array(
            'barName' => array(
                'stdClass' => 'barStorage', 'FooClass' => 'FooStorage'
            ),
            'fooName' => array(
                'stdClass' => 'fooStorage', 'FooClass' => 'FooStorage'
            )
        );

        $paymentName = 'fooName';
        $storageName = 'barName';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));

        $this->assertEquals($storages['barName'], $registry->getStorages());
    }

    /**
     * @test
     */
    public function shouldAllowGetStoragesForNameGiven()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array(
            'barName' => array(
                'stdClass' => 'barStorage', 'FooClass' => 'FooStorage'
            ),
            'fooName' => array(
                'stdClass' => 'fooStorage', 'FooClass' => 'FooStorage'
            )
        );

        $paymentName = 'fooName';
        $storageName = 'barName';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));

        $this->assertEquals($storages['barName'], $registry->getStorages('barName'));
        $this->assertEquals($storages['fooName'], $registry->getStorages('fooName'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Payum storages named notExistName do not exist.
     */
    public function throwIfTryToGetStoragesWithNotExistName()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array(
            'barName' => array(
                'stdClass' => 'barStorage', 'FooClass' => 'FooStorage'
            ),
        );

        $paymentName = 'fooName';
        $storageName = 'barName';

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
            $paymentName,
            $storageName
        ));
        
        $registry->getStorages('notExistName');
    }

    /**
     * @param array $constructorArguments
     * 
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\Registry\AbstractRegistry
     */
    protected function createAbstractRegistryMock(array $constructorArguments)
    {
        $registryMock = $this->getMockForAbstractClass('Payum\Core\Registry\AbstractRegistry', $constructorArguments);
        
        $registryMock
            ->expects($this->any())
            ->method('getService')
            ->will($this->returnArgument(0))
        ;
        
        return $registryMock;
    }
}