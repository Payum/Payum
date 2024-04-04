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
use PHPUnit\Framework\TestCase;

class DynamicRegistryTest extends TestCase
{
    public function testShouldImplementsRegistryInterface()
    {
        $rc = new \ReflectionClass(DynamicRegistry::class);

        $this->assertTrue($rc->implementsInterface(RegistryInterface::class));
    }

    /**
     * @deprecated
     */
    public function testShouldCallStaticRegistryOnGetGateways()
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

    public function testShouldReturnEmptyArrayOnGetGatewaysIfNothingFound()
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

    public function testShouldCreateGatewaysUsingConfigOnGetGateways()
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
     */
    public function testShouldCreateGatewayUsingConfigAndGetFactoryNameOnGetGateway()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig($config = array('foo' => 'fooVal', 'bar' => 'barVal'));
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
            ->with(array('gatewayName' => $gatewayName))
            ->willReturn(array($gatewayConfig))
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame($gateway, $registry->getGateway($gatewayName));
    }

    public function testShouldCreateGatewayUsingConfigOnGetGateway()
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
            ->with(array('gatewayName' => $gatewayName))
            ->willReturn(array($gatewayConfig))
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame($gateway, $registry->getGateway($gatewayName));
    }

    public function testShouldCreateGatewayOnlyOnceWhenCalledMultipleTimes()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig($config = array('foo' => 'fooVal', 'bar' => 'barVal'));
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
            ->with(array('gatewayName' => $gatewayName))
            ->willReturn(array($gatewayConfig))
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);

        $this->assertSame($gateway, $registry->getGateway($gatewayName));
        $this->assertSame($gateway, $registry->getGateway($gatewayName));
    }

    /**
     * @deprecated
     */
    public function testShouldCallStaticRegistryIfGatewayConfigNotFoundOnGetGateway()
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

    public function testThrowIfGatewayConfigNotFoundOnGetGateway()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
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
            ->with(array('gatewayName' => 'theGatewayName'))
            ->willReturn(null)
        ;

        $registry = new DynamicRegistry($storageMock, $gatewayFactoryRegistry);
        $registry->setBackwardCompatibility(false);

        $registry->getGateway('theGatewayName');
    }

    /**
     * @deprecated
     */
    public function testShouldCallStaticRegistryOnGetGatewayFactories()
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

        $this->assertSame('theGatewaysFactories', $registry->getGatewayFactories());
    }

    public function testShouldReturnEmptyArrayOnGetGatewayFactories()
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
    public function testShouldCallStaticRegistryOnGetGatewayFactory()
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

        $this->assertSame('theGatewayFactory', $registry->getGatewayFactory('theName'));
    }

    public function testAlwaysThrowOnGetGatewayFactory()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
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
    public function testShouldCallStaticRegistryOnGetStorages()
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

        $this->assertSame('theStorages', $registry->getStorages());
    }

    public function testShouldReturnEmptyArrayOnGetStorages()
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
    public function testShouldCallStaticRegistryOnGetStorage()
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

        $this->assertSame('theStorage', $registry->getStorage('theName'));
    }

    public function testAlwaysThrowOnGetStorageForClass()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage for given class "theClass" does not exist.');
        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $this->createGatewayFactoryRegistryMock()
        );
        $registry->setBackwardCompatibility(false);

        $registry->getStorage('theClass');
    }

    public function testAlwaysThrowOnGetStorageForObject()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage for given class "stdClass" does not exist.');
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
        return $this->createMock(StorageInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    protected function createRegistryMock()
    {
        return $this->createMock(RegistryInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayFactoryRegistryInterface
     */
    protected function createGatewayFactoryRegistryMock()
    {
        return $this->createMock(GatewayFactoryRegistryInterface::class);
    }
}
