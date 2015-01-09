<?php
namespace Payum\Core\Tests\Registry;

use Doctrine\Common\Persistence\Proxy;

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
    public function shouldImplementPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\PaymentFactoryRegistryInterface'));
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
    public function couldConstructedWithoutAnyArguments()
    {
        $registry = $this->createAbstractRegistryMock(array());

        $this->assertAttributeEquals(array(), 'payments', $registry);
        $this->assertAttributeEquals(array(), 'storages', $registry);
        $this->assertAttributeEquals(array(), 'paymentFactories', $registry);
    }

    /**
     * @test
     */
    public function couldConstructedWithPaymentsOnly()
    {
        $payments = array('fooName' => 'fooPayment');

        $registry = $this->createAbstractRegistryMock(array($payments));

        $this->assertAttributeEquals($payments, 'payments', $registry);
        $this->assertAttributeEquals(array(), 'storages', $registry);
        $this->assertAttributeEquals(array(), 'paymentFactories', $registry);
    }

    /**
     * @test
     */
    public function couldConstructedWithStoragesOnly()
    {
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(array(), $storages));

        $this->assertAttributeEquals(array(), 'payments', $registry);
        $this->assertAttributeEquals($storages, 'storages', $registry);
        $this->assertAttributeEquals(array(), 'paymentFactories', $registry);
    }

    /**
     * @test
     */
    public function couldConstructedWithPaymentFactoriesOnly()
    {
        $factories = array('bar' => 'barFactory');

        $registry = $this->createAbstractRegistryMock(array(array(), array(), $factories));

        $this->assertAttributeEquals(array(), 'payments', $registry);
        $this->assertAttributeEquals(array(), 'storages', $registry);
        $this->assertAttributeEquals($factories, 'paymentFactories', $registry);
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentWithNamePassedExplicitly()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
        ));

        $this->assertEquals('barPayment', $registry->getPayment('barName'));
    }

    /**
     * @test
     */
    public function shouldAllowGetAllPayments()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
        ));

        $payments = $registry->getPayments();

        $this->assertInternalType('array', $payments);
        $this->assertCount(2, $payments);

        $this->assertArrayHasKey('fooName', $payments);
        $this->assertEquals('fooPayment', $payments['fooName']);

        $this->assertArrayHasKey('barName', $payments);
        $this->assertEquals('barPayment', $payments['barName']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Payment "notExistName" does not exist.
     */
    public function throwIfTryToGetPaymentWithNotExistName()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
        ));

        $registry->getPayment('notExistName');
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentFactoryByName()
    {
        $paymentFactories = array('foo' => 'fooPaymentFactory', 'bar' => 'barPaymentFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $paymentFactories,
        ));

        $this->assertEquals('barPaymentFactory', $registry->getPaymentFactory('bar'));
    }

    /**
     * @test
     */
    public function shouldAllowGetAllPaymentFactories()
    {
        $paymentFactories = array('foo' => 'fooPaymentFactory', 'bar' => 'barPaymentFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $paymentFactories,
        ));

        $payments = $registry->getPaymentFactories();

        $this->assertInternalType('array', $payments);
        $this->assertCount(2, $payments);

        $this->assertArrayHasKey('foo', $payments);
        $this->assertEquals('fooPaymentFactory', $payments['foo']);

        $this->assertArrayHasKey('bar', $payments);
        $this->assertEquals('barPaymentFactory', $payments['bar']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Payment factory "notExistName" does not exist.
     */
    public function throwIfTryToGetPaymentFactoryWithNotExistName()
    {
        $paymentFactories = array('foo' => 'fooPaymentFactory', 'bar' => 'barPaymentFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $paymentFactories
        ));

        $registry->getPaymentFactory('notExistName');
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageForGivenModelClass()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
        ));

        $this->assertEquals('barStorage', $registry->getStorage('stdClass'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageIfDoctrineProxyClassGiven()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('Payum\Core\Tests\Registry\DoctrineModel' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
        ));

        $this->assertEquals('barStorage', $registry->getStorage('Payum\Core\Tests\Registry\DoctrineProxy'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageIfDoctrineProxyObjectGiven()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('Payum\Core\Tests\Registry\DoctrineModel' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
        ));

        $this->assertEquals('barStorage', $registry->getStorage(new DoctrineProxy()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage A storage for model notRegisteredModelClass was not registered. There are storages for next models: stdClass.
     */
    public function throwIfTryToGetStorageWithNotRegisteredModelClass()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
        ));

        $this->assertEquals('barStorage', $registry->getStorage('notRegisteredModelClass'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageWithObjectModel()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
        ));

        $this->assertEquals('barStorage', $registry->getStorage(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorages()
    {
        $payments = array('fooName' => 'fooPayment', 'barName' => 'barPayment');
        $storages = array(
            'stdClass' => 'barStorage', 'FooClass' => 'FooStorage',
        );

        $registry = $this->createAbstractRegistryMock(array(
            $payments,
            $storages,
        ));

        $this->assertEquals($storages, $registry->getStorages());
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

class DoctrineModel
{
}

class DoctrineProxy extends DoctrineModel implements Proxy
{
    public function __load()
    {
    }

    public function __isInitialized()
    {
    }
}
