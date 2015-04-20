<?php
namespace Payum\Core\Tests\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Model\GatewayConfig;
use Payum\Core\Gateway;
use Payum\Core\Registry\DynamicRegistry;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Storage\StorageInterface;

class DynamicRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\DynamicRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\RegistryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithGatewayConfigAndStaticStorageAsArguments()
    {
        new DynamicRegistry($this->createStorageMock(), $this->createRegistryMock());
    }

    /**
     * @test
     */
    public function shouldCallStaticRegistryOnGetGateways()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getGateways')
            ->willReturn('theGateways')
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );
        
        $this->assertEquals('theGateways', $registry->getGateways());
    }

    /**
     * @test
     */
    public function shouldCreateGatewayUsingConfigOnGetGateway()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig($config = array('foo' => 'fooVal', 'bar' => 'barVal'));
        $gatewayConfig->setFactoryName($factoryName = 'theFactoryName');
        $gatewayConfig->setGatewayName($gatewayName = 'theGatewayName');

        $gateway = new Gateway();

        $gatewayFactoryMock = $this->getMock('Payum\Core\GatewayFactoryInterface');
        $gatewayFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($config)
            ->willReturn($gateway)
        ;

        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
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

        $registry = new DynamicRegistry($storageMock, $staticRegistryMock);

        $this->assertSame($gateway, $registry->getGateway($gatewayName));
    }

    /**
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
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->getMock('Payum\Core\Storage\StorageInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    protected function createRegistryMock()
    {
        return $this->getMock('Payum\Core\Registry\RegistryInterface');
    }
}
