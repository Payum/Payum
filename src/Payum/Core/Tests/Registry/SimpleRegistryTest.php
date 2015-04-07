<?php
namespace Payum\Core\Tests\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Registry\SimpleRegistry;

class SimpleRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractRegistry()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\SimpleRegistry');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\AbstractRegistry'));
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
        $gatewayFooMock = $this->getMock('Payum\Core\Gateway');
        $gatewayBarMock = $this->getMock('Payum\Core\Gateway');

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
        $gatewayFooMock = $this->getMock('Payum\Core\Gateway');
        $gatewayBarMock = $this->getMock('Payum\Core\Gateway');

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
        $storageFooMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageBarMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $registry = new SimpleRegistry(
            array(),
            array(
                'stdClass' => $storageFooMock,
                'Payum\Core\Tests\Mocks\Model\TestModel' => $storageBarMock,
            )
        );

        $this->assertSame($storageFooMock, $registry->getStorage('stdClass'));
        $this->assertSame($storageBarMock, $registry->getStorage('Payum\Core\Tests\Mocks\Model\TestModel'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorages()
    {
        $storageFooMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageBarMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $storages = array(
            'stdClass' => $storageFooMock,
            'Payum\Core\Tests\Mocks\Model\TestModel' => $storageBarMock,
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
        $storageMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $testCase = $this;

        $gatewayMock = $this->getMock('Payum\Core\Gateway');
        $gatewayMock
            ->expects($this->once())
            ->method('addExtension')
            ->with($this->isInstanceOf('Payum\Core\Extension\StorageExtension'))
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
        $gatewayMock = $this->getMock('Payum\Core\Gateway');
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
        $storageOneMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageTwoMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageThreeMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $gatewayFooMock = $this->getMock('Payum\Core\Gateway');
        $gatewayFooMock
            ->expects($this->exactly(3))
            ->method('addExtension')
        ;

        $gatewayBarMock = $this->getMock('Payum\Core\Gateway');
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
    public function shouldInitializeStorageExtensionsOnGetGateways()
    {
        $storageOneMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $gatewayFooMock = $this->getMock('Payum\Core\Gateway');
        $gatewayFooMock
            ->expects($this->once())
            ->method('addExtension')
        ;

        $gatewayBarMock = $this->getMock('Payum\Core\Gateway');
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
        $storageOneMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $gatewayFooMock = $this->getMock('Payum\Core\GatewayInterface');

        $gatewayBarMock = $this->getMock('Payum\Core\Gateway');

        $registry = new SimpleRegistry(
            array('foo' => $gatewayFooMock, 'bar' => $gatewayBarMock),
            array(
                'fooClass' => $storageOneMock,
            )
        );

        $registry->getGateways();
    }
}
