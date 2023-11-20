<?php

namespace Payum\Core\Tests\Bridge\Symfony;

use Payum\Core\Bridge\Symfony\ContainerAwareRegistry;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Registry\AbstractRegistry;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class ContainerAwareRegistryTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractRegistry(): void
    {
        $rc = new ReflectionClass(ContainerAwareRegistry::class);

        $this->assertTrue($rc->isSubclassOf(AbstractRegistry::class));
    }

    public function testShouldImplementContainerAwareInterface(): void
    {
        $rc = new ReflectionClass(ContainerAwareRegistry::class);

        $this->assertTrue($rc->implementsInterface(ContainerAwareInterface::class));
    }

    public function testShouldReturnGatewaySetToContainer(): void
    {
        $gateways = [
            'fooGateway' => 'fooGatewayServiceId',
        ];
        $storages = [];

        $container = new Container();
        $container->set('fooGatewayServiceId', $this->createMock(GatewayInterface::class));

        $registry = new ContainerAwareRegistry($gateways, $storages);
        $registry->setContainer($container);

        $this->assertSame(
            $container->get('fooGatewayServiceId'),
            $registry->getGateway('fooGateway')
        );
    }

    public function testShouldReturnStorageSetToContainer(): void
    {
        $gateways = [];
        $storages = [
            stdClass::class => 'fooStorageServiceId',
        ];

        $container = new Container();
        $container->set('fooStorageServiceId', $this->createMock(StorageInterface::class));

        $registry = new ContainerAwareRegistry($gateways, $storages);
        $registry->setContainer($container);

        $this->assertSame($container->get('fooStorageServiceId'), $registry->getStorage(stdClass::class));
    }

    public function testShouldReturnGatewayFactorySetToContainer(): void
    {
        $container = new Container();
        $container->set(GatewayFactoryInterface::class, $this->createMock(GatewayFactoryInterface::class));

        $registry = new ContainerAwareRegistry([], [], [
            'fooName' => GatewayFactoryInterface::class,
        ]);
        $registry->setContainer($container);

        $this->assertSame($container->get(GatewayFactoryInterface::class), $registry->getGatewayFactory('fooName'));
    }
}
