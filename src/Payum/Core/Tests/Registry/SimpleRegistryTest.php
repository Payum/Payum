<?php
namespace Payum\Core\Tests\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;
use Payum\Core\GatewayInterface;
use Payum\Core\Registry\AbstractRegistry;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\Mocks\Model\TestModel;
use PHPUnit\Framework\TestCase;

class SimpleRegistryTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractRegistry()
    {
        $rc = new \ReflectionClass(SimpleRegistry::class);

        $this->assertTrue($rc->isSubclassOf(AbstractRegistry::class));
    }

    public function testShouldAllowGetGatewaySetInConstructor()
    {
        $gatewayFooMock = $this->createMock(GatewayInterface::class);
        $gatewayBarMock = $this->createMock(GatewayInterface::class);

        $registry = new SimpleRegistry(
            array('foo' => $gatewayFooMock, 'bar' => $gatewayBarMock)
        );

        $this->assertSame($gatewayFooMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayBarMock, $registry->getGateway('bar'));
    }

    public function testShouldAllowGetGatewaysSetInConstructor()
    {
        $gatewayFooMock = $this->createMock(GatewayInterface::class);
        $gatewayBarMock = $this->createMock(GatewayInterface::class);

        $registry = new SimpleRegistry(
            array('foo' => $gatewayFooMock, 'bar' => $gatewayBarMock)
        );

        $gateways = $registry->getGateways();

        $this->assertContains($gatewayFooMock, $gateways);
        $this->assertContains($gatewayBarMock, $gateways);
    }

    public function testShouldAllowGetStorageForGivenClass()
    {
        $storageFooMock = $this->createMock(StorageInterface::class);
        $storageBarMock = $this->createMock(StorageInterface::class);

        $registry = new SimpleRegistry(
            array(),
            array(
                'stdClass' => $storageFooMock,
                TestModel::class => $storageBarMock,
            )
        );

        $this->assertSame($storageFooMock, $registry->getStorage('stdClass'));
        $this->assertSame($storageBarMock, $registry->getStorage(TestModel::class));
    }

    public function testShouldAllowGetStorages()
    {
        $storageFooMock = $this->createMock(StorageInterface::class);
        $storageBarMock = $this->createMock(StorageInterface::class);

        $storages = array(
            'stdClass' => $storageFooMock,
            TestModel::class => $storageBarMock,
        );

        $registry = new SimpleRegistry(
            array(),
            $storages
        );

        $this->assertEquals($storages, $registry->getStorages());
    }

    public function testShouldInitializeStorageExtensionOnlyOnFirstCallGetGateway()
    {
        $storageMock = $this->createMock(StorageInterface::class);

        $gatewayMock = $this->createMock(Gateway::class);
        $gatewayMock
            ->expects($this->once())
            ->method('addExtension')
            ->with($this->isInstanceOf(StorageExtension::class))
        ;

        $registry = new SimpleRegistry(
            array('foo' => $gatewayMock),
            array('stdClass' => $storageMock)
        );

        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
    }

    public function testShouldNotInitializeStorageExtensionsIfAnyStoragesAssociatedWithGateway()
    {
        $gatewayMock = $this->createMock(GatewayInterface::class);

        $registry = new SimpleRegistry(array('foo' => $gatewayMock));

        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
    }

    public function testShouldInitializeStorageExtensionsForEachStorageInRegistry()
    {
        $storageOneMock = $this->createMock(StorageInterface::class);
        $storageTwoMock = $this->createMock(StorageInterface::class);
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
            array('foo' => $gatewayFooMock, 'bar' => $gatewayBarMock),
            array(
                'fooClass' => $storageOneMock,
                'barClass' => $storageTwoMock,
                'ololClass' => $storageThreeMock,
            )
        );

        $this->assertSame($gatewayFooMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayBarMock, $registry->getGateway('bar'));
    }

    public function testShouldNotInitializeStorageExtensionsIfAddStorageExtensionsSetFalse()
    {
        $storageOneMock = $this->createMock(StorageInterface::class);
        $storageTwoMock = $this->createMock(StorageInterface::class);
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
            array('foo' => $gatewayFooMock, 'bar' => $gatewayBarMock),
            array(
                'fooClass' => $storageOneMock,
                'barClass' => $storageTwoMock,
                'ololClass' => $storageThreeMock,
            )
        );
        $registry->setAddStorageExtensions(false);

        $registry->getGateway('foo');
        $registry->getGateway('bar');
    }

    public function testShouldInitializeStorageExtensionsOnGetGateways()
    {
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
            array('foo' => $gatewayFooMock, 'bar' => $gatewayBarMock),
            array(
                'fooClass' => $storageOneMock,
            )
        );

        $registry->getGateways();
        $registry->getGateways();
    }

    public function testShouldNotInitializeStorageExtensionsOnGetGatewaysIfNotGenericGateway()
    {
        $storageOneMock = $this->createMock(StorageInterface::class);

        $gatewayFooMock = $this->createMock(GatewayInterface::class);

        $gatewayBarMock = $this->createMock(GatewayInterface::class);

        $registry = new SimpleRegistry(
            array('foo' => $gatewayFooMock, 'bar' => $gatewayBarMock),
            array(
                'fooClass' => $storageOneMock,
            )
        );

        $this->assertSame(['foo' => $gatewayFooMock, 'bar' => $gatewayBarMock], $registry->getGateways());

        $this->assertSame(['fooClass' => $storageOneMock], $registry->getStorages());
    }
}
