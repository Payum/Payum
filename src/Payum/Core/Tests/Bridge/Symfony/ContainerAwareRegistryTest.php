<?php

namespace Payum\Core\Tests\Bridge\Symfony;

use Payum\Core\Bridge\Symfony\ContainerAwareRegistry;
use Payum\Core\GatewayInterface;
use Payum\Core\Registry\AbstractRegistry;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class ContainerAwareRegistryTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractRegistry()
    {
        $rc = new \ReflectionClass(ContainerAwareRegistry::class);

        $this->assertTrue($rc->isSubclassOf(AbstractRegistry::class));
    }

    public function testShouldImplementContainerAwareInterface()
    {
        $rc = new \ReflectionClass(ContainerAwareRegistry::class);

        $this->assertTrue($rc->implementsInterface(ContainerAwareInterface::class));
    }

    public function testShouldReturnGatewaySetToContainer()
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

    public function testShouldReturnStorageSetToContainer()
    {
        $gateways = [];
        $storages = [
            \stdClass::class => 'fooStorageServiceId',
        ];

        $container = new Container();
        $container->set('fooStorageServiceId', $this->createMock(StorageInterface::class));

        $registry = new ContainerAwareRegistry($gateways, $storages);
        $registry->setContainer($container);

        $this->assertSame($container->get('fooStorageServiceId'), $registry->getStorage(\stdClass::class));
    }

    public function testShouldReturnGatewayFactorySetToContainer()
    {
        $container = new Container();
        $container->set('fooFactoryServiceId', $this->createMock(StorageInterface::class));

        $registry = new ContainerAwareRegistry([], [], [
            'fooName' => 'fooFactoryServiceId',
        ]);
        $registry->setContainer($container);

        $this->assertSame($container->get('fooFactoryServiceId'), $registry->getGatewayFactory('fooName'));
    }
}
