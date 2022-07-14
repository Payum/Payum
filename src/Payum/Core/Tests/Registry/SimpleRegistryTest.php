<?php

namespace Payum\Core\Tests\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;
use Payum\Core\GatewayInterface;
use Payum\Core\Registry\AbstractRegistry;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\Mocks\Model\TestModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class SimpleRegistryTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractRegistry(): void
    {
        $rc = new ReflectionClass(SimpleRegistry::class);

        $this->assertTrue($rc->isSubclassOf(AbstractRegistry::class));
    }

    public function testShouldAllowGetGatewaySetInConstructor(): void
    {
        $gatewayFooMock = $this->createMock(GatewayInterface::class);
        $gatewayBarMock = $this->createMock(GatewayInterface::class);

        $registry = new SimpleRegistry(
            [
                'foo' => $gatewayFooMock,
                'bar' => $gatewayBarMock,
            ]
        );

        $this->assertSame($gatewayFooMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayBarMock, $registry->getGateway('bar'));
    }

    public function testShouldAllowGetGatewaysSetInConstructor(): void
    {
        $gatewayFooMock = $this->createMock(GatewayInterface::class);
        $gatewayBarMock = $this->createMock(GatewayInterface::class);

        $registry = new SimpleRegistry(
            [
                'foo' => $gatewayFooMock,
                'bar' => $gatewayBarMock,
            ]
        );

        $gateways = $registry->getGateways();

        $this->assertContains($gatewayFooMock, $gateways);
        $this->assertContains($gatewayBarMock, $gateways);
    }

    public function testShouldAllowGetStorageForGivenClass(): void
    {
        /** @var MockObject | StorageInterface<stdClass> $storageFooMock */
        $storageFooMock = $this->createMock(StorageInterface::class);

        /** @var MockObject | StorageInterface<TestModel> $storageBarMock */
        $storageBarMock = $this->createMock(StorageInterface::class);

        $registry = new SimpleRegistry(
            [],
            [
                stdClass::class => $storageFooMock,
                TestModel::class => $storageBarMock,
            ]
        );

        $this->assertSame($storageFooMock, $registry->getStorage(stdClass::class));
        $this->assertSame($storageBarMock, $registry->getStorage(TestModel::class));
    }

    public function testShouldAllowGetStorages(): void
    {
        /** @var MockObject | StorageInterface<stdClass> $storageFooMock */
        $storageFooMock = $this->createMock(StorageInterface::class);

        /** @var MockObject | StorageInterface<TestModel> $storageBarMock */
        $storageBarMock = $this->createMock(StorageInterface::class);

        $storages = [
            stdClass::class => $storageFooMock,
            TestModel::class => $storageBarMock,
        ];

        $registry = new SimpleRegistry(
            [],
            $storages
        );

        $this->assertEquals($storages, $registry->getStorages());
    }

    public function testShouldInitializeStorageExtensionOnlyOnFirstCallGetGateway(): void
    {
        /** @var MockObject | StorageInterface<stdClass> $storageMock */
        $storageMock = $this->createMock(StorageInterface::class);

        $gatewayMock = $this->createMock(Gateway::class);
        $gatewayMock
            ->expects($this->once())
            ->method('addExtension')
            ->with($this->isInstanceOf(StorageExtension::class))
        ;

        $registry = new SimpleRegistry(
            [
                'foo' => $gatewayMock,
            ],
            [
                stdClass::class => $storageMock,
            ]
        );

        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
    }

    public function testShouldNotInitializeStorageExtensionsIfAnyStoragesAssociatedWithGateway(): void
    {
        $gatewayMock = $this->createMock(GatewayInterface::class);

        $registry = new SimpleRegistry([
            'foo' => $gatewayMock,
        ]);

        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
    }

    public function testShouldInitializeStorageExtensionsForEachStorageInRegistry(): void
    {
        /** @var MockObject | StorageInterface<stdClass> $storageOneMock */
        $storageOneMock = $this->createMock(StorageInterface::class);

        /** @var MockObject | StorageInterface<TestModel> $storageTwoMock */
        $storageTwoMock = $this->createMock(StorageInterface::class);

        /** @var MockObject | StorageInterface<TestCase> $storageThreeMock */
        $storageThreeMock = $this->createMock(StorageInterface::class);

        $gatewayFooMock = $this->createMock(Gateway::class);
        $gatewayFooMock
            ->expects($this->exactly(3))
            ->method('addExtension')
        ;

        $gatewayBarMock = $this->createMock(Gateway::class);
        $gatewayBarMock
            ->expects($this->exactly(3))
            ->method('addExtension')
        ;

        $registry = new SimpleRegistry(
            [
                'foo' => $gatewayFooMock,
                'bar' => $gatewayBarMock,
            ],
            [
                stdClass::class => $storageOneMock,
                TestModel::class => $storageTwoMock,
                TestCase::class => $storageThreeMock,
            ]
        );

        $this->assertSame($gatewayFooMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayBarMock, $registry->getGateway('bar'));
    }

    public function testShouldNotInitializeStorageExtensionsIfAddStorageExtensionsSetFalse(): void
    {
        /** @var MockObject | StorageInterface<stdClass> $storageOneMock */
        $storageOneMock = $this->createMock(StorageInterface::class);

        /** @var MockObject | StorageInterface<TestModel> $storageTwoMock */
        $storageTwoMock = $this->createMock(StorageInterface::class);

        /** @var MockObject | StorageInterface<TestCase> $storageThreeMock */
        $storageThreeMock = $this->createMock(StorageInterface::class);

        $gatewayFooMock = $this->createMock(Gateway::class);
        $gatewayFooMock
            ->expects($this->never())
            ->method('addExtension')
        ;

        $gatewayBarMock = $this->createMock(Gateway::class);
        $gatewayBarMock
            ->expects($this->never())
            ->method('addExtension')
        ;

        $registry = new SimpleRegistry(
            [
                'foo' => $gatewayFooMock,
                'bar' => $gatewayBarMock,
            ],
            [
                stdClass::class => $storageOneMock,
                TestModel::class => $storageTwoMock,
                TestCase::class => $storageThreeMock,
            ]
        );
        $registry->setAddStorageExtensions(false);

        $registry->getGateway('foo');
        $registry->getGateway('bar');
    }

    public function testShouldInitializeStorageExtensionsOnGetGateways(): void
    {
        /** @var MockObject | StorageInterface<stdClass> $storageOneMock */
        $storageOneMock = $this->createMock(StorageInterface::class);

        $gatewayFooMock = $this->createMock(Gateway::class);
        $gatewayFooMock
            ->expects($this->once())
            ->method('addExtension')
        ;

        $gatewayBarMock = $this->createMock(Gateway::class);
        $gatewayBarMock
            ->expects($this->once())
            ->method('addExtension')
        ;

        $registry = new SimpleRegistry(
            [
                'foo' => $gatewayFooMock,
                'bar' => $gatewayBarMock,
            ],
            [
                stdClass::class => $storageOneMock,
            ]
        );

        $registry->getGateways();
        $registry->getGateways();
    }

    public function testShouldNotInitializeStorageExtensionsOnGetGatewaysIfNotGenericGateway(): void
    {
        /** @var MockObject | StorageInterface<stdClass> $storageOneMock */
        $storageOneMock = $this->createMock(StorageInterface::class);

        $gatewayFooMock = $this->createMock(GatewayInterface::class);

        $gatewayBarMock = $this->createMock(GatewayInterface::class);

        $registry = new SimpleRegistry(
            [
                'foo' => $gatewayFooMock,
                'bar' => $gatewayBarMock,
            ],
            [
                stdClass::class => $storageOneMock,
            ]
        );

        $this->assertSame([
            'foo' => $gatewayFooMock,
            'bar' => $gatewayBarMock,
        ], $registry->getGateways());

        $this->assertSame([
            stdClass::class => $storageOneMock,
        ], $registry->getStorages());
    }
}
