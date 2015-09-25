<?php
namespace Payum\Core\Tests\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;
use Payum\Core\GatewayInterface;
use Payum\Core\Registry\AbstractRegistry;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\Mocks\Model\TestModel;

class SimpleRegistryTest extends \PHPUnit_Framework_TestCase
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
        $gatewayFooMock = $this->getMock(GatewayInterface::class);
        $gatewayBarMock = $this->getMock(GatewayInterface::class);

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
        $gatewayFooMock = $this->getMock(GatewayInterface::class);
        $gatewayBarMock = $this->getMock(GatewayInterface::class);

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
        $storageFooMock = $this->getMock(StorageInterface::class);
        $storageBarMock = $this->getMock(StorageInterface::class);

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
        $storageFooMock = $this->getMock(StorageInterface::class);
        $storageBarMock = $this->getMock(StorageInterface::class);

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
        $storageMock = $this->getMock(StorageInterface::class);

        $testCase = $this;

        $gatewayMock = $this->getMock(Gateway::class);
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
        $gatewayMock = $this->getMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->never())
            ->method('addExtension')
        ;

        $registry = new SimpleRegistry(array('foo' => $gatewayMock));

        $registry->getGateway('foo');
    }

    /**
     * @test
     */
    public function shouldInitializeStorageExtensionsForEachStorageInRegistry()
    {
        $storageOneMock = $this->getMock(StorageInterface::class);
        $storageTwoMock = $this->getMock(StorageInterface::class);
        $storageThreeMock = $this->getMock(StorageInterface::class);

        $gatewayFooMock = $this->getMock(Gateway::class);
        $gatewayFooMock
            ->expects($this->exactly(3))
            ->method('addExtension')
        ;

        $gatewayBarMock = $this->getMock(Gateway::class);
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
        $storageOneMock = $this->getMock(StorageInterface::class);
        $storageTwoMock = $this->getMock(StorageInterface::class);
        $storageThreeMock = $this->getMock(StorageInterface::class);

        $gatewayFooMock = $this->getMock(Gateway::class);
        $gatewayFooMock
            ->expects($this->never())
            ->method('addExtension')
        ;

        $gatewayBarMock = $this->getMock(Gateway::class);
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
        $storageOneMock = $this->getMock(StorageInterface::class);

        $gatewayFooMock = $this->getMock(Gateway::class);
        $gatewayFooMock
            ->expects($this->once())
            ->method('addExtension')
        ;

        $gatewayBarMock = $this->getMock(Gateway::class);
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
        $storageOneMock = $this->getMock(StorageInterface::class);

        $gatewayFooMock = $this->getMock(GatewayInterface::class);

        $gatewayBarMock = $this->getMock(GatewayInterface::class);

        $registry = new SimpleRegistry(
            array('foo' => $gatewayFooMock, 'bar' => $gatewayBarMock),
            array(
                'fooClass' => $storageOneMock,
            )
        );

        $registry->getGateways();
    }
}
