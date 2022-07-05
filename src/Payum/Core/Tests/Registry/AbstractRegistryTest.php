<?php

namespace Payum\Core\Tests\Registry;

use Doctrine\Persistence\Proxy;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Registry\AbstractRegistry;
use Payum\Core\Registry\GatewayFactoryRegistryInterface;
use Payum\Core\Registry\GatewayRegistryInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AbstractRegistryTest extends TestCase
{
    public function testShouldImplementGatewayRegistryInterface()
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->implementsInterface(GatewayRegistryInterface::class));
    }

    public function testShouldImplementStorageRegistryInterface()
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->implementsInterface(StorageRegistryInterface::class));
    }

    public function testShouldImplementGatewayFactoryInterface()
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryRegistryInterface::class));
    }

    public function testShouldBeAbstractClass()
    {
        $rc = new ReflectionClass(AbstractRegistry::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testShouldAllowGetGatewayWithNamePassedExplicitly()
    {
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
        ]);

        $this->assertSame('barGateway', $registry->getGateway('barName'));
    }

    public function testShouldAllowGetAllGateways()
    {
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
        ]);

        $gateways = $registry->getGateways();

        $this->assertIsArray($gateways);
        $this->assertCount(2, $gateways);

        $this->assertArrayHasKey('fooName', $gateways);
        $this->assertSame('fooGateway', $gateways['fooName']);

        $this->assertArrayHasKey('barName', $gateways);
        $this->assertSame('barGateway', $gateways['barName']);
    }

    public function testThrowIfTryToGetGatewayWithNotExistName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway "notExistName" does not exist.');
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
        ]);

        $registry->getGateway('notExistName');
    }

    public function testShouldAllowGetGatewayFactoryByName()
    {
        $gatewayFactories = [
            'foo' => 'fooGatewayFactory',
            'bar' => 'barGatewayFactory',
        ];

        $registry = $this->createAbstractRegistryMock([
            [],
            [],
            $gatewayFactories,
        ]);

        $this->assertSame('barGatewayFactory', $registry->getGatewayFactory('bar'));
    }

    public function testShouldAllowGetAllGatewayFactories()
    {
        $gatewayFactories = [
            'foo' => 'fooGatewayFactory',
            'bar' => 'barGatewayFactory',
        ];

        $registry = $this->createAbstractRegistryMock([
            [],
            [],
            $gatewayFactories,
        ]);

        $gateways = $registry->getGatewayFactories();

        $this->assertIsArray($gateways);
        $this->assertCount(2, $gateways);

        $this->assertArrayHasKey('foo', $gateways);
        $this->assertSame('fooGatewayFactory', $gateways['foo']);

        $this->assertArrayHasKey('bar', $gateways);
        $this->assertSame('barGatewayFactory', $gateways['bar']);
    }

    public function testThrowIfTryToGetGatewayFactoryWithNotExistName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gateway factory "notExistName" does not exist.');
        $gatewayFactories = [
            'foo' => 'fooGatewayFactory',
            'bar' => 'barGatewayFactory',
        ];

        $registry = $this->createAbstractRegistryMock([
            [],
            [],
            $gatewayFactories,
        ]);

        $registry->getGatewayFactory('notExistName');
    }

    public function testShouldAllowGetStorageForGivenModelClass()
    {
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => 'barStorage',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame('barStorage', $registry->getStorage(stdClass::class));
    }

    public function testShouldAllowGetStorageIfDoctrineProxyClassGiven()
    {
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            'Payum\Core\Tests\Registry\DoctrineModel' => 'barStorage',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame('barStorage', $registry->getStorage('Payum\Core\Tests\Registry\DoctrineProxy'));
    }

    public function testShouldAllowGetStorageIfDoctrineProxyObjectGiven()
    {
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            'Payum\Core\Tests\Registry\DoctrineModel' => 'barStorage',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame('barStorage', $registry->getStorage(new DoctrineProxy()));
    }

    public function testThrowIfTryToGetStorageWithNotRegisteredModelClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A storage for model notRegisteredModelClass was not registered. There are storages for next models: stdClass.');
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => 'barStorage',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame('barStorage', $registry->getStorage('notRegisteredModelClass'));
    }

    public function testShouldAllowGetStorageWithObjectModel()
    {
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => 'barStorage',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertSame('barStorage', $registry->getStorage(new stdClass()));
    }

    public function testShouldAllowGetStorages()
    {
        $gateways = [
            'fooName' => 'fooGateway',
            'barName' => 'barGateway',
        ];
        $storages = [
            stdClass::class => 'barStorage',
            'FooClass' => 'FooStorage',
        ];

        $registry = $this->createAbstractRegistryMock([
            $gateways,
            $storages,
        ]);

        $this->assertEquals($storages, $registry->getStorages());
    }

    /**
     * @return MockObject|AbstractRegistry
     */
    protected function createAbstractRegistryMock(array $constructorArguments)
    {
        $registryMock = $this->getMockForAbstractClass(AbstractRegistry::class, $constructorArguments);

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
