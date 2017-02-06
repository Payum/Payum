<?php
namespace Payum\Core\Tests\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Model\GatewayConfig;
use Payum\Core\Gateway;
use Payum\Core\Registry\DynamicRegistry;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Storage\StorageInterface;

class DynamicRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsRegistryInterface()
    {
        $rc = new \ReflectionClass(DynamicRegistry::class);

        $this->assertTrue($rc->implementsInterface(RegistryInterface::class));
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function couldBeConstructedWithGatewayConfigAndRegistryAsArguments()
    {
        new DynamicRegistry($this->createStorageMock(), $this->createRegistryMock());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithGatewayConfigAndGatewayFactoryRegistryAsArguments()
    {
        new DynamicRegistry($this->createStorageMock(), $this->createGatewayFactoryRegistryMock());
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function shouldCallStaticRegistryOnGetGateways()
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
        
        $this->assertEquals(['theGateways'], $registry->getGateways());
    }

    /**
     * @test
     */
    public function shouldReturnEmptyArrayOnGetGatewaysIfNothingFound()
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

    /**
     * @test
     */
    public function shouldCreateGatewaysUsingConfigOnGetGateways()
    {
        $factoryName = 'theFactoryName';

        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig(array(
            'factory' => $factoryName,
            'foo' => 'fooVal',
            'bar' => 'barVal')
        );
        $gatewayConfig->setGatewayName($gatewayName = 'theGatewayName');

        $config = array('foo' => 'fooVal', 'bar' => 'barVal');

        $gateway = new Gateway();

        $gatewayFactoryMock = $this->getMock(GatewayFactoryInterface::class);
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
            ->expects($this->at(0))
            ->method('findBy')
            ->with([])
            ->willReturn([$gatewayConfig])
        ;
        $storageMock
            ->expects($this->at(1))
            ->method('findBy')
            ->with(['gatewayName' => $gatewayName])
            ->willReturn([$gatewayConfig])
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame([$gatewayName => $gateway], $registry->getGateways());
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function shouldCreateGatewayUsingConfigAndGetFactoryNameOnGetGateway()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig($config = array('foo' => 'fooVal', 'bar' => 'barVal'));
        $gatewayConfig->setFactoryName($factoryName = 'theFactoryName');
        $gatewayConfig->setGatewayName($gatewayName = 'theGatewayName');

        $gateway = new Gateway();

        $gatewayFactoryMock = $this->getMock(GatewayFactoryInterface::class);
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
            ->with(array('gatewayName' => $gatewayName))
            ->willReturn(array($gatewayConfig))
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame($gateway, $registry->getGateway($gatewayName));
    }

    /**
     * @test
     */
    public function shouldCreateGatewayUsingConfigOnGetGateway()
    {
        $factoryName = 'theFactoryName';

        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig(array(
            'factory' => $factoryName,
            'foo' => 'fooVal',
            'bar' => 'barVal')
        );
        $gatewayConfig->setGatewayName($gatewayName = 'theGatewayName');

        $config = array('foo' => 'fooVal', 'bar' => 'barVal');

        $gateway = new Gateway();

        $gatewayFactoryMock = $this->getMock(GatewayFactoryInterface::class);
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
            ->with(array('gatewayName' => $gatewayName))
            ->willReturn(array($gatewayConfig))
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame($gateway, $registry->getGateway($gatewayName));
    }

    /**
     * @test
     */
    public function shouldCreateGatewayOnlyOnceWhenCalledMultipleTimes()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig($config = array('foo' => 'fooVal', 'bar' => 'barVal'));
        $gatewayConfig->setFactoryName($factoryName = 'theFactoryName');
        $gatewayConfig->setGatewayName($gatewayName = 'theGatewayName');

        $gateway = new Gateway();

        $gatewayFactoryMock = $this->getMock(GatewayFactoryInterface::class);
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
            ->with(array('gatewayName' => $gatewayName))
            ->willReturn(array($gatewayConfig))
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame($gateway, $registry->getGateway($gatewayName));
        $this->assertSame($gateway, $registry->getGateway($gatewayName));
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function shouldCallStaticRegistryIfGatewayConfigNotFoundOnGetGateway()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getGateway')
            ->with('theGatewayName')
            ->willReturn('theGateway')
        ;
        $staticRegistryMock
            ->expects($this->never())
            ->method('getGatewayFactory')
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findBy')
            ->with(array('gatewayName' => 'theGatewayName'))
            ->willReturn(null)
        ;

        $registry = new DynamicRegistry($storageMock, $staticRegistryMock);

        $this->assertSame('theGateway', $registry->getGateway('theGatewayName'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Gateway "theGatewayName" does not exist.
     */
    public function throwIfGatewayConfigNotFoundOnGetGateway()
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
            ->with(array('gatewayName' => 'theGatewayName'))
            ->willReturn(null)
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);
        $registry->setBackwardCompatibility(false);

        $registry->getGateway('theGatewayName');
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function shouldCallStaticRegistryOnGetGatewayFactories()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactories')
            ->willReturn('theGatewaysFactories')
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertEquals('theGatewaysFactories', $registry->getGatewayFactories());
    }

    /**
     * @test
     */
    public function shouldReturnEmptyArrayOnGetGatewayFactories()
    {
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $this->assertEquals([], $registry->getGatewayFactories());
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function shouldCallStaticRegistryOnGetGatewayFactory()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getGatewayFactory')
            ->with('theName')
            ->willReturn('theGatewayFactory')
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertEquals('theGatewayFactory', $registry->getGatewayFactory('theName'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Gateway factory "theName" does not exist.
     */
    public function alwaysThrowOnGetGatewayFactory()
    {
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $registry->getGatewayFactory('theName');
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function shouldCallStaticRegistryOnGetStorages()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getStorages')
            ->willReturn('theStorages')
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertEquals('theStorages', $registry->getStorages());
    }

    /**
     * @test
     */
    public function shouldReturnEmptyArrayOnGetStorages()
    {
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $this->assertEquals([], $registry->getStorages());
    }

    /**
     * @deprecated
     *
     * @test
     */
    public function shouldCallStaticRegistryOnGetStorage()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with('theName')
            ->willReturn('theStorage')
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertEquals('theStorage', $registry->getStorage('theName'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Storage for given class "theClass" does not exist.
     */
    public function alwaysThrowOnGetStorageForClass()
    {
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $registry->getStorage('theClass');
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Storage for given class "stdClass" does not exist.
     */
    public function alwaysThrowOnGetStorageForObject()
    {
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $registry->getStorage(new \stdClass);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->getMock(StorageInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    protected function createRegistryMock()
    {
        return $this->getMock(RegistryInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayFactoryRegistryInterface
     */
    protected function createGatewayFactoryRegistryMock()
    {
        return $this->getMock(GatewayFactoryRegistryInterface::class);
    }
}
