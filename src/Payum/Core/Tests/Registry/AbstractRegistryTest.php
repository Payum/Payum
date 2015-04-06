<?php
namespace Payum\Core\Tests\Registry;

use Doctrine\Common\Persistence\Proxy;

class AbstractRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\GatewayRegistryInterface'));
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
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\GatewayFactoryRegistryInterface'));
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

        $this->assertAttributeEquals(array(), 'gateways', $registry);
        $this->assertAttributeEquals(array(), 'storages', $registry);
        $this->assertAttributeEquals(array(), 'gatewayFactories', $registry);
    }

    /**
     * @test
     */
    public function couldConstructedWithGatewaysOnly()
    {
        $gateways = array('fooName' => 'fooGateway');

        $registry = $this->createAbstractRegistryMock(array($gateways));

        $this->assertAttributeEquals($gateways, 'gateways', $registry);
        $this->assertAttributeEquals(array(), 'storages', $registry);
        $this->assertAttributeEquals(array(), 'gatewayFactories', $registry);
    }

    /**
     * @test
     */
    public function couldConstructedWithStoragesOnly()
    {
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(array(), $storages));

        $this->assertAttributeEquals(array(), 'gateways', $registry);
        $this->assertAttributeEquals($storages, 'storages', $registry);
        $this->assertAttributeEquals(array(), 'gatewayFactories', $registry);
    }

    /**
     * @test
     */
    public function couldConstructedWithGatewayFactoriesOnly()
    {
        $factories = array('bar' => 'barFactory');

        $registry = $this->createAbstractRegistryMock(array(array(), array(), $factories));

        $this->assertAttributeEquals(array(), 'gateways', $registry);
        $this->assertAttributeEquals(array(), 'storages', $registry);
        $this->assertAttributeEquals($factories, 'gatewayFactories', $registry);
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewayWithNamePassedExplicitly()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
        ));

        $this->assertEquals('barGateway', $registry->getGateway('barName'));
    }

    /**
     * @test
     */
    public function shouldAllowGetAllGateways()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
        ));

        $gateways = $registry->getGateways();

        $this->assertInternalType('array', $gateways);
        $this->assertCount(2, $gateways);

        $this->assertArrayHasKey('fooName', $gateways);
        $this->assertEquals('fooGateway', $gateways['fooName']);

        $this->assertArrayHasKey('barName', $gateways);
        $this->assertEquals('barGateway', $gateways['barName']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Gateway "notExistName" does not exist.
     */
    public function throwIfTryToGetGatewayWithNotExistName()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
        ));

        $registry->getGateway('notExistName');
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewayFactoryByName()
    {
        $gatewayFactories = array('foo' => 'fooGatewayFactory', 'bar' => 'barGatewayFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $gatewayFactories,
        ));

        $this->assertEquals('barGatewayFactory', $registry->getGatewayFactory('bar'));
    }

    /**
     * @test
     */
    public function shouldAllowGetAllGatewayFactories()
    {
        $gatewayFactories = array('foo' => 'fooGatewayFactory', 'bar' => 'barGatewayFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $gatewayFactories,
        ));

        $gateways = $registry->getGatewayFactories();

        $this->assertInternalType('array', $gateways);
        $this->assertCount(2, $gateways);

        $this->assertArrayHasKey('foo', $gateways);
        $this->assertEquals('fooGatewayFactory', $gateways['foo']);

        $this->assertArrayHasKey('bar', $gateways);
        $this->assertEquals('barGatewayFactory', $gateways['bar']);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Gateway factory "notExistName" does not exist.
     */
    public function throwIfTryToGetGatewayFactoryWithNotExistName()
    {
        $gatewayFactories = array('foo' => 'fooGatewayFactory', 'bar' => 'barGatewayFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $gatewayFactories
        ));

        $registry->getGatewayFactory('notExistName');
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageForGivenModelClass()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertEquals('barStorage', $registry->getStorage('stdClass'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageIfDoctrineProxyClassGiven()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('Payum\Core\Tests\Registry\DoctrineModel' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertEquals('barStorage', $registry->getStorage('Payum\Core\Tests\Registry\DoctrineProxy'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageIfDoctrineProxyObjectGiven()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('Payum\Core\Tests\Registry\DoctrineModel' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
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
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertEquals('barStorage', $registry->getStorage('notRegisteredModelClass'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageWithObjectModel()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertEquals('barStorage', $registry->getStorage(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorages()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array(
            'stdClass' => 'barStorage', 'FooClass' => 'FooStorage',
        );

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
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
