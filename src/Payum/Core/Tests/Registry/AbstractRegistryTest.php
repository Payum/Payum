<?php
namespace Payum\Core\Tests\Registry;

use Doctrine\Persistence\Proxy;
use PHPUnit\Framework\TestCase;

class AbstractRegistryTest extends TestCase
{
    public function testShouldImplementGatewayRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\GatewayRegistryInterface'));
    }

    public function testShouldImplementStorageRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\StorageRegistryInterface'));
    }

    public function testShouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\GatewayFactoryRegistryInterface'));
    }

    public function testShouldBeAbstractClass()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->isAbstract());
    }

    public function testShouldAllowGetGatewayWithNamePassedExplicitly()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
        ));

        $this->assertSame('barGateway', $registry->getGateway('barName'));
    }

    public function testShouldAllowGetAllGateways()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
        ));

        $gateways = $registry->getGateways();

        $this->assertIsArray($gateways);
        $this->assertCount(2, $gateways);

        $this->assertArrayHasKey('fooName', $gateways);
        $this->assertSame('fooGateway', $gateways['fooName']);

        $this->assertArrayHasKey('barName', $gateways);
        $this->assertSame('barGateway', $gateways['barName']);
    }

    public function testThrowIfTryToGetGatewayWithNotExistName()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway "notExistName" does not exist.');
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
        ));

        $registry->getGateway('notExistName');
    }

    public function testShouldAllowGetGatewayFactoryByName()
    {
        $gatewayFactories = array('foo' => 'fooGatewayFactory', 'bar' => 'barGatewayFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $gatewayFactories,
        ));

        $this->assertSame('barGatewayFactory', $registry->getGatewayFactory('bar'));
    }

    public function testShouldAllowGetAllGatewayFactories()
    {
        $gatewayFactories = array('foo' => 'fooGatewayFactory', 'bar' => 'barGatewayFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $gatewayFactories,
        ));

        $gateways = $registry->getGatewayFactories();

        $this->assertIsArray($gateways);
        $this->assertCount(2, $gateways);

        $this->assertArrayHasKey('foo', $gateways);
        $this->assertSame('fooGatewayFactory', $gateways['foo']);

        $this->assertArrayHasKey('bar', $gateways);
        $this->assertSame('barGatewayFactory', $gateways['bar']);
    }

    public function testThrowIfTryToGetGatewayFactoryWithNotExistName()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway factory "notExistName" does not exist.');
        $gatewayFactories = array('foo' => 'fooGatewayFactory', 'bar' => 'barGatewayFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $gatewayFactories
        ));

        $registry->getGatewayFactory('notExistName');
    }

    public function testShouldAllowGetStorageForGivenModelClass()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame('barStorage', $registry->getStorage('stdClass'));
    }

    public function testShouldAllowGetStorageIfDoctrineProxyClassGiven()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('Payum\Core\Tests\Registry\DoctrineModel' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame('barStorage', $registry->getStorage('Payum\Core\Tests\Registry\DoctrineProxy'));
    }

    public function testShouldAllowGetStorageIfDoctrineProxyObjectGiven()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('Payum\Core\Tests\Registry\DoctrineModel' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame('barStorage', $registry->getStorage(new DoctrineProxy()));
    }

    public function testThrowIfTryToGetStorageWithNotRegisteredModelClass()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('A storage for model notRegisteredModelClass was not registered. There are storages for next models: stdClass.');
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame('barStorage', $registry->getStorage('notRegisteredModelClass'));
    }

    public function testShouldAllowGetStorageWithObjectModel()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame('barStorage', $registry->getStorage(new \stdClass()));
    }

    public function testShouldAllowGetStorages()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array(
            'stdClass' => 'barStorage', 'FooClass' => 'FooStorage',
        );

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame($storages, $registry->getStorages());
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
            ->method('getService')
            ->willReturnArgument(0)
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

