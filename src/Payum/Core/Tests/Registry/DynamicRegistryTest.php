<?php

namespace Payum\Core\Tests\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\GatewayConfig;
use Payum\Core\Registry\DynamicRegistry;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class DynamicRegistryTest extends TestCase
{
    public function testShouldImplementsRegistryInterface(): void
    {
        $rc = new ReflectionClass(DynamicRegistry::class);

        $this->assertTrue($rc->implementsInterface(RegistryInterface::class));
    }

    /**
     * @deprecated
     */
    public function testShouldCallStaticRegistryOnGetGateways(): void
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getGateways')
            ->willReturn(['theGateways'])
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertSame(['theGateways'], $registry->getGateways());
    }

    public function testShouldReturnEmptyArrayOnGetGatewaysIfNothingFound(): void
    {
        $gatewayFactoryRegistry = $this->createGatewayFactoryRegistryMock();
        $gatewayFactoryRegistry
            ->expects($this->never())
            ->method('getGatewayFactory')
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findBy')
            ->with([])
            ->willReturn([])
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame([], $registry->getGateways());
    }

    public function testShouldCreateGatewaysUsingConfigOnGetGateways(): void
    {
        $factoryName = 'theFactoryName';

        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig(
            [
                'factory' => $factoryName,
                'foo' => 'fooVal',
                'bar' => 'barVal',
            ]
        );
        $gatewayConfig->setGatewayName($gatewayName = 'theGatewayName');

        $config = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];

        $gateway = new Gateway();

        $gatewayFactoryMock = $this->createMock(GatewayFactoryInterface::class);
        $gatewayFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($config)
            ->willReturn($gateway)
        ;

        $gatewayFactoryRegistry = $this->createGatewayFactoryRegistryMock();
        $gatewayFactoryRegistry
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with($factoryName)
            ->willReturn($gatewayFactoryMock)
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeast(2))
            ->method('findBy')
            ->withConsecutive([[]], [[
                'gatewayName' => $gatewayName,
            ]])
            ->willReturn([$gatewayConfig])
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame([
            $gatewayName => $gateway,
        ], $registry->getGateways());
    }

    /**
     * @deprecated
     */
    public function testShouldCreateGatewayUsingConfigAndGetFactoryNameOnGetGateway(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig($config = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);
        $gatewayConfig->setFactoryName($factoryName = 'theFactoryName');
        $gatewayConfig->setGatewayName($gatewayName = 'theGatewayName');

        $gateway = new Gateway();

        $gatewayFactoryMock = $this->createMock(GatewayFactoryInterface::class);
        $gatewayFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($config)
            ->willReturn($gateway)
        ;

        $gatewayFactoryRegistry = $this->createGatewayFactoryRegistryMock();
        $gatewayFactoryRegistry
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with($factoryName)
            ->willReturn($gatewayFactoryMock)
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findBy')
            ->with([
                'gatewayName' => $gatewayName,
            ])
            ->willReturn([$gatewayConfig])
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame($gateway, $registry->getGateway($gatewayName));
    }

    public function testShouldCreateGatewayUsingConfigOnGetGateway(): void
    {
        $factoryName = 'theFactoryName';

        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig(
            [
                'factory' => $factoryName,
                'foo' => 'fooVal',
                'bar' => 'barVal',
            ]
        );
        $gatewayConfig->setGatewayName($gatewayName = 'theGatewayName');

        $config = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];

        $gateway = new Gateway();

        $gatewayFactoryMock = $this->createMock(GatewayFactoryInterface::class);
        $gatewayFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($config)
            ->willReturn($gateway)
        ;

        $gatewayFactoryRegistry = $this->createGatewayFactoryRegistryMock();
        $gatewayFactoryRegistry
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with($factoryName)
            ->willReturn($gatewayFactoryMock)
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findBy')
            ->with([
                'gatewayName' => $gatewayName,
            ])
            ->willReturn([$gatewayConfig])
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame($gateway, $registry->getGateway($gatewayName));
    }

    public function testShouldCreateGatewayOnlyOnceWhenCalledMultipleTimes(): void
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig($config = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);
        $gatewayConfig->setFactoryName($factoryName = 'theFactoryName');
        $gatewayConfig->setGatewayName($gatewayName = 'theGatewayName');

        $gateway = new Gateway();

        $gatewayFactoryMock = $this->createMock(GatewayFactoryInterface::class);
        $gatewayFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($config)
            ->willReturn($gateway)
        ;

        $gatewayFactoryRegistry = $this->createGatewayFactoryRegistryMock();
        $gatewayFactoryRegistry
            ->expects($this->atLeastOnce())
            ->method('getGatewayFactory')
            ->with($factoryName)
            ->willReturn($gatewayFactoryMock)
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->atLeastOnce())
            ->method('findBy')
            ->with([
                'gatewayName' => $gatewayName,
            ])
            ->willReturn([$gatewayConfig])
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame($gateway, $registry->getGateway($gatewayName));
        $this->assertSame($gateway, $registry->getGateway($gatewayName));
    }

    /**
     * @deprecated
     */
    public function testShouldCallStaticRegistryIfGatewayConfigNotFoundOnGetGateway(): void
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->willReturn($gateway)
        ;
        $staticRegistryMock
            ->expects($this->never())
            ->method('getGatewayFactory')
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findBy')
            ->with([
                'gatewayName' => 'theGatewayName',
            ])
            ->willReturn(null)
        ;

        $registry = new DynamicRegistry($storageMock, $staticRegistryMock);

        $this->assertSame($gateway, $registry->getGateway('theGatewayName'));
    }

    public function testThrowIfGatewayConfigNotFoundOnGetGateway(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway "theGatewayName" does not exist.');
        $gatewayFactoryRegistry = $this->createGatewayFactoryRegistryMock();
        $gatewayFactoryRegistry
            ->expects($this->never())
            ->method('getGatewayFactory')
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findBy')
            ->with([
                'gatewayName' => 'theGatewayName',
            ])
            ->willReturn(null)
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);
        $registry->setBackwardCompatibility(false);

        $registry->getGateway('theGatewayName');
    }

    /**
     * @deprecated
     */
    public function testShouldCallStaticRegistryOnGetGatewayFactories(): void
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactories')
            ->willReturn(['theGatewaysFactories'])
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertSame(['theGatewaysFactories'], $registry->getGatewayFactories());
    }

    public function testShouldReturnEmptyArrayOnGetGatewayFactories(): void
    {
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $this->assertSame([], $registry->getGatewayFactories());
    }

    /**
     * @deprecated
     */
    public function testShouldCallStaticRegistryOnGetGatewayFactory(): void
    {
        $gatewayFactory = $this->createMock(GatewayFactoryInterface::class);
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with('theName')
            ->willReturn($gatewayFactory)
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertSame($gatewayFactory, $registry->getGatewayFactory('theName'));
    }

    public function testAlwaysThrowOnGetGatewayFactory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway factory "theName" does not exist.');
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $registry->getGatewayFactory('theName');
    }

    /**
     * @deprecated
     */
    public function testShouldCallStaticRegistryOnGetStorages(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getStorages')
            ->willReturn([$storage])
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertSame([$storage], $registry->getStorages());
    }

    public function testShouldReturnEmptyArrayOnGetStorages(): void
    {
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $this->assertSame([], $registry->getStorages());
    }

    /**
     * @deprecated
     */
    public function testShouldCallStaticRegistryOnGetStorage(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with(stdClass::class)
            ->willReturn($storage)
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertSame($storage, $registry->getStorage(stdClass::class));
    }

    public function testAlwaysThrowOnGetStorageForClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage for given class "stdClass" does not exist.');
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $registry->getStorage(stdClass::class);
    }

    public function testAlwaysThrowOnGetStorageForObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage for given class "stdClass" does not exist.');
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $registry->getStorage(new stdClass());
    }

    /**
     * @return MockObject|StorageInterface<object>
     */
    protected function createStorageMock(): StorageInterface | MockObject
    {
        return $this->createMock(StorageInterface::class);
    }

    /**
     * @return MockObject|RegistryInterface<object>
     */
    protected function createRegistryMock(): MockObject | RegistryInterface
    {
        return $this->createMock(RegistryInterface::class);
    }

    protected function createGatewayFactoryRegistryMock(): MockObject | GatewayFactoryRegistryInterface
    {
        return $this->createMock(GatewayFactoryRegistryInterface::class);
    }
}
