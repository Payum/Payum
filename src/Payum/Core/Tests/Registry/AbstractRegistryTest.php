<?php

namespace Payum\Core\Tests\Registry;

use Doctrine\Persistence\Proxy;
use PHPUnit\Framework\TestCase;

class AbstractRegistryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\GatewayRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementStorageRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\StorageRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\GatewayFactoryRegistryInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstractClass()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\AbstractRegistry');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewayWithNamePassedExplicitly()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
        ));

        $this->assertSame('barGateway', $registry->getGateway('barName'));
    }

    /**
     * @test
     */
    public function shouldAllowGetAllGateways()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
        ));

        $gateways = $registry->getGateways();

        $this->assertIsArray($gateways);
        $this->assertCount(2, $gateways);

        $this->assertArrayHasKey('fooName', $gateways);
        $this->assertSame('fooGateway', $gateways['fooName']);

        $this->assertArrayHasKey('barName', $gateways);
        $this->assertSame('barGateway', $gateways['barName']);
    }

    /**
     * @test
     */
    public function throwIfTryToGetGatewayWithNotExistName()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway "notExistName" does not exist.');
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
        ));

        $registry->getGateway('notExistName');
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewayFactoryByName()
    {
        $gatewayFactories = array('foo' => 'fooGatewayFactory', 'bar' => 'barGatewayFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $gatewayFactories,
        ));

        $this->assertSame('barGatewayFactory', $registry->getGatewayFactory('bar'));
    }

    /**
     * @test
     */
    public function shouldAllowGetAllGatewayFactories()
    {
        $gatewayFactories = array('foo' => 'fooGatewayFactory', 'bar' => 'barGatewayFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $gatewayFactories,
        ));

        $gateways = $registry->getGatewayFactories();

        $this->assertIsArray($gateways);
        $this->assertCount(2, $gateways);

        $this->assertArrayHasKey('foo', $gateways);
        $this->assertSame('fooGatewayFactory', $gateways['foo']);

        $this->assertArrayHasKey('bar', $gateways);
        $this->assertSame('barGatewayFactory', $gateways['bar']);
    }

    /**
     * @test
     */
    public function throwIfTryToGetGatewayFactoryWithNotExistName()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway factory "notExistName" does not exist.');
        $gatewayFactories = array('foo' => 'fooGatewayFactory', 'bar' => 'barGatewayFactory');

        $registry = $this->createAbstractRegistryMock(array(
            array(),
            array(),
            $gatewayFactories
        ));

        $registry->getGatewayFactory('notExistName');
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageForGivenModelClass()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame('barStorage', $registry->getStorage('stdClass'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageIfDoctrineProxyClassGiven()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('Payum\Core\Tests\Registry\DoctrineModel' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame('barStorage', $registry->getStorage('Payum\Core\Tests\Registry\DoctrineProxy'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageIfDoctrineProxyObjectGiven()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('Payum\Core\Tests\Registry\DoctrineModel' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame('barStorage', $registry->getStorage(new DoctrineProxy()));
    }

    /**
     * @test
     */
    public function throwIfTryToGetStorageWithNotRegisteredModelClass()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('A storage for model notRegisteredModelClass was not registered. There are storages for next models: stdClass.');
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame('barStorage', $registry->getStorage('notRegisteredModelClass'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageWithObjectModel()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array('stdClass' => 'barStorage');

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertSame('barStorage', $registry->getStorage(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorages()
    {
        $gateways = array('fooName' => 'fooGateway', 'barName' => 'barGateway');
        $storages = array(
            'stdClass' => 'barStorage', 'FooClass' => 'FooStorage',
        );

        $registry = $this->createAbstractRegistryMock(array(
            $gateways,
            $storages,
        ));

        $this->assertEquals($storages, $registry->getStorages());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Core\Registry\AbstractRegistry
     */
    protected function createAbstractRegistryMock(array $constructorArguments)
    {
        $registryMock = $this->getMockForAbstractClass('Payum\Core\Registry\AbstractRegistry', $constructorArguments);

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
    public function __load()
    {
    }

    public function __isInitialized()
    {
    }
}
