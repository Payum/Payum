<?php

namespace Payum\Core\Tests\Registry;

use Doctrine\Persistence\Proxy;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Registry\AbstractRegistry;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Payum\Core\Registry\GatewayRegistryInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AbstractRegistryTest extends TestCase
{
    public function testShouldImplementGatewayRegistryInterface()
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->implementsInterface(GatewayRegistryInterface::class));
    }

    public function testShouldImplementStorageRegistryInterface()
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->implementsInterface(StorageRegistryInterface::class));
    }

    public function testShouldImplementGatewayFactoryInterface()
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryRegistryInterface::class));
    }

    public function testShouldBeAbstractClass()
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testShouldAllowGetGatewayWithNamePassedExplicitly()
    {
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
        ]);

        $this->assertSame('barGateway', $registry->getGateway('barName'));
    }

    public function testShouldAllowGetAllGateways()
    {
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
        ]);

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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway "notExistName" does not exist.');
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
        ]);

        $registry->getGateway('notExistName');
    }

    public function testShouldAllowGetGatewayFactoryByName()
    {
        $gatewayFactories = [
            'foo' => 'fooGatewayFactory',
            'bar' => 'barGatewayFactory',
        ];

        $registry = $this->createAbstractRegistryMock([
            [],
            [],
            $gatewayFactories,
        ]);

        $this->assertSame('barGatewayFactory', $registry->getGatewayFactory('bar'));
    }

    public function testShouldAllowGetAllGatewayFactories()
    {
        $gatewayFactories = [
            'foo' => 'fooGatewayFactory',
            'bar' => 'barGatewayFactory',
        ];

        $registry = $this->createAbstractRegistryMock([
            [],
            [],
            $gatewayFactories,
        ]);

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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway factory "notExistName" does not exist.');
        $gatewayFactories = [
            'foo' => 'fooGatewayFactory',
            'bar' => 'barGatewayFactory',
        ];

        $registry = $this->createAbstractRegistryMock([
            [],
            [],
            $gatewayFactories,
        ]);

        $registry->getGatewayFactory('notExistName');
    }

    public function testShouldAllowGetStorageForGivenModelClass()
    {
        /** @var MockObject | StorageInterface<stdClass> $storageMock */
        $storageMock = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => $storageMock,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame($storageMock, $registry->getStorage(stdClass::class));
    }

    public function testShouldAllowGetStorageIfDoctrineProxyClassGiven()
    {
        /** @var MockObject | StorageInterface<DoctrineModel | DoctrineProxy> $storage */
        $storage = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            DoctrineModel::class => $storage,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame($storage, $registry->getStorage(DoctrineProxy::class));
    }

    public function testShouldAllowGetStorageIfDoctrineProxyObjectGiven()
    {
        /** @var MockObject | StorageInterface<DoctrineModel> $storage */
        $storage = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            DoctrineModel::class => $storage,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame($storage, $registry->getStorage(DoctrineProxy::class));
    }

    public function testThrowIfTryToGetStorageWithNotRegisteredModelClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A storage for model Payum\Core\Tests\Registry\DoctrineModel was not registered. There are storages for next models: stdClass.');

        /** @var MockObject | StorageInterface<stdClass> $storage */
        $storage = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => $storage,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $registry->getStorage(\Payum\Core\Tests\Registry\DoctrineModel::class);
    }

    public function testShouldAllowGetStorageWithObjectModel()
    {
        /** @var MockObject | StorageInterface<stdClass> $storage */
        $storage = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => $storage,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame($storage, $registry->getStorage(stdClass::class));
    }

    public function testShouldAllowGetStorages()
    {
        /** @var MockObject | StorageInterface<stdClass> $storageOne */
        $storageOne = $this->createMock(StorageInterface::class);

        /** @var MockObject | StorageInterface<DoctrineModel> $storageTwo */
        $storageTwo = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => $storageOne,
            DoctrineModel::class => $storageTwo,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertEquals($storages, $registry->getStorages());
    }

    /**
     * @return MockObject | AbstractRegistry<stdClass | DoctrineModel | DoctrineProxy>
     */
    protected function createAbstractRegistryMock(array $constructorArguments)
    {
        $registryMock = $this->getMockForAbstractClass(AbstractRegistry::class, $constructorArguments);

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
