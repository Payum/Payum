<?php

namespace Payum\Core\Tests\Registry;

use Doctrine\Persistence\Proxy;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
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
    public function testShouldImplementGatewayRegistryInterface(): void
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->implementsInterface(GatewayRegistryInterface::class));
    }

    public function testShouldImplementStorageRegistryInterface(): void
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->implementsInterface(StorageRegistryInterface::class));
    }

    public function testShouldImplementGatewayFactoryInterface(): void
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryRegistryInterface::class));
    }

    public function testShouldBeAbstractClass(): void
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testShouldAllowGetGatewayWithNamePassedExplicitly(): void
    {
        $fooGateway = $this->createMock(GatewayInterface::class);
        $barGateway = $this->createMock(GatewayInterface::class);

        $gateways = [
            'fooName' => $fooGateway,
            'barName' => $barGateway,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
        ]);

        $this->assertSame($barGateway, $registry->getGateway('barName'));
    }

    public function testShouldAllowGetAllGateways(): void
    {
        $fooGateway = $this->createMock(GatewayInterface::class);
        $barGateway = $this->createMock(GatewayInterface::class);

        $gateways = [
            'fooName' => $fooGateway,
            'barName' => $barGateway,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
        ]);

        $gateways = $registry->getGateways();

        $this->assertIsArray($gateways);
        $this->assertCount(2, $gateways);

        $this->assertArrayHasKey('fooName', $gateways);
        $this->assertSame($fooGateway, $gateways['fooName']);

        $this->assertArrayHasKey('barName', $gateways);
        $this->assertSame($barGateway, $gateways['barName']);
    }

    public function testThrowIfTryToGetGatewayWithNotExistName(): void
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

    public function testShouldAllowGetGatewayFactoryByName(): void
    {
        $fooGatewayFactory = $this->createMock(GatewayFactoryInterface::class);
        $barGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $gatewayFactories = [
            'foo' => $fooGatewayFactory,
            'bar' => $barGatewayFactory,
        ];

        $registry = $this->createAbstractRegistryMock([
            [],
            [],
            $gatewayFactories,
        ]);

        $this->assertSame($barGatewayFactory, $registry->getGatewayFactory('bar'));
    }

    public function testShouldAllowGetAllGatewayFactories(): void
    {
        $fooGatewayFactory = $this->createMock(GatewayFactoryInterface::class);
        $barGatewayFactory = $this->createMock(GatewayFactoryInterface::class);

        $gatewayFactories = [
            'foo' => $fooGatewayFactory,
            'bar' => $barGatewayFactory,
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
        $this->assertSame($fooGatewayFactory, $gateways['foo']);

        $this->assertArrayHasKey('bar', $gateways);
        $this->assertSame($barGatewayFactory, $gateways['bar']);
    }

    public function testThrowIfTryToGetGatewayFactoryWithNotExistName(): void
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

    public function testShouldAllowGetStorageForGivenModelClass(): void
    {
        $barStorage = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => $barStorage,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame($barStorage, $registry->getStorage(stdClass::class));
    }

    public function testShouldAllowGetStorageIfDoctrineProxyClassGiven(): void
    {
        $barStorage = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            DoctrineModel::class => $barStorage,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame($barStorage, $registry->getStorage(DoctrineProxy::class));
    }

    public function testShouldAllowGetStorageIfDoctrineProxyObjectGiven(): void
    {
        $barStorage = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            DoctrineModel::class => $barStorage,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame($barStorage, $registry->getStorage(new DoctrineProxy()));
    }

    public function testThrowIfTryToGetStorageWithNotRegisteredModelClass(): void
    {
        $class = new class() {
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A storage for model ' . $class::class . ' was not registered. There are storages for next models: stdClass.');

        $barStorage = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => $barStorage,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $registry->getStorage($class);
    }

    public function testShouldAllowGetStorageWithObjectModel(): void
    {
        $barStorage = $this->createMock(StorageInterface::class);

        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => $barStorage,
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame($barStorage, $registry->getStorage(new stdClass()));
    }

    public function testShouldAllowGetStorages(): void
    {
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => 'barStorage',
            'FooClass' => 'FooStorage',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame($storages, $registry->getStorages());
    }

    /**
     * @return MockObject|AbstractRegistry<object>
     */
    protected function createAbstractRegistryMock(array $constructorArguments): AbstractRegistry | MockObject
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
    public function __load(): void
    {
    }

    public function __isInitialized(): bool
    {
        return true;
    }
}
