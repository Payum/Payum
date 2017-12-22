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
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractRegistry()
    {
        $rc = new \ReflectionClass(SimpleRegistry::class);

        $this->assertTrue($rc->isSubclassOf(AbstractRegistry::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        $registry = new SimpleRegistry();

        $this->assertAttributeEquals(array(), 'gateways', $registry);
        $this->assertAttributeEquals(array(), 'storages', $registry);
        $this->assertAttributeEquals(array(), 'gatewayFactories', $registry);
    }

    /**
     * @test
     */
    public function couldBeConstructedWithAllPossibleArguments()
    {
        $registry = new SimpleRegistry(
            $gateways = array('foo' => 'fooGateway'),
            $storages = array('fooClass' => 'fooStorage'),
            $gatewayFactories = array('bar' => 'barFactory')
        );

        $this->assertAttributeEquals($gateways, 'gateways', $registry);
        $this->assertAttributeEquals($storages, 'storages', $registry);
        $this->assertAttributeEquals($gatewayFactories, 'gatewayFactories', $registry);
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewaySetInConstructor()
    {
        $gatewayFooMock = $this->createMock(GatewayInterface::class);
        $gatewayBarMock = $this->createMock(GatewayInterface::class);

        $registry = new SimpleRegistry(
            array('foo' => $gatewayFooMock, 'bar' => $gatewayBarMock)
        );

        $this->assertSame($gatewayFooMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayBarMock, $registry->getGateway('bar'));
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewaysSetInConstructor()
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

    /**
     * @test
     */
    public function shouldAllowGetStorageForGivenClass()
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

    /**
     * @test
     */
    public function shouldAllowGetStorages()
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

    /**
     * @test
     */
    public function shouldInitializeStorageExtensionOnlyOnFirstCallGetGateway()
    {
        $storageMock = $this->createMock(StorageInterface::class);

        $testCase = $this;

        $gatewayMock = $this->createMock(Gateway::class);
        $gatewayMock
            ->expects($this->once())
            ->method('addExtension')
            ->with($this->isInstanceOf(StorageExtension::class))
            ->will($this->returnCallback(function (StorageExtension $extension) use ($storageMock, $testCase) {
                $testCase->assertAttributeSame($storageMock, 'storage', $extension);
            }))
        ;

        $registry = new SimpleRegistry(
            array('foo' => $gatewayMock),
            array('stdClass' => $storageMock)
        );

        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
        $this->assertSame($gatewayMock, $registry->getGateway('foo'));
    }

    /**
     * @test
     */
    public function shouldNotInitializeStorageExtensionsIfAnyStoragesAssociatedWithGateway()
    {
        $gatewayMock = $this->createMock(GatewayInterface::class);

        $registry = new SimpleRegistry(array('foo' => $gatewayMock));

        $registry->getGateway('foo');
    }

    /**
     * @test
     */
    public function shouldInitializeStorageExtensionsForEachStorageInRegistry()
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

        $registry->getGateway('foo');
        $registry->getGateway('bar');
    }

    /**
     * @test
     */
    public function shouldNotInitializeStorageExtensionsIfAddStorageExtensionsSetFalse()
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

    /**
     * @test
     */
    public function shouldInitializeStorageExtensionsOnGetGateways()
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

    /**
     * @test
     */
    public function shouldNotInitializeStorageExtensionsOnGetGatewaysIfNotGenericGateway()
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

        $registry->getGateways();
    }
}
