<?php

namespace Payum\Core\Tests\Bridge\Symfony;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Symfony\ContainerAwareCoreGatewayFactory;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class ContainerAwareCoreGatewayFactoryTest extends TestCase
{
    public function testShouldExtendCoreGatewayFactory(): void
    {
        $rc = new ReflectionClass(ContainerAwareCoreGatewayFactory::class);

        $this->assertTrue($rc->isSubclassOf(CoreGatewayFactory::class));
    }

    public function testShouldImplementContainerAwareInterface(): void
    {
        $rc = new ReflectionClass(ContainerAwareCoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(ContainerAwareInterface::class));
    }

    public function testShouldResolveContainerParameter(): void
    {
        $container = new Container();
        $container->setParameter('foo', 'fooVal');
        $container->setParameter('bar.baz_ololo', 'barBazOloloVal');

        $factory = new ContainerAwareCoreGatewayFactory();
        $factory->setContainer($container);

        $called = false;

        $factory->create([
            'foo' => '%foo%',
            'bar' => '%bar.baz_ololo%',
            'test' => function (ArrayObject $config) use (&$called): void {
                $called = true;

                $this->assertSame('fooVal', $config['foo']);
                $this->assertSame('barBazOloloVal', $config['bar']);
            },
        ]);

        $this->assertTrue($called);
    }

    public function testShouldResolveTemplateFromContainerParameter(): void
    {
        $container = new Container();
        $container->setParameter('a_template_parameter', '@aTemplate');

        $factory = new ContainerAwareCoreGatewayFactory();
        $factory->setContainer($container);

        $called = false;

        $factory->create([
            'payum.template.foo' => '%a_template_parameter%',
            'test' => function (ArrayObject $config) use (&$called): void {
                $called = true;

                $this->assertSame('@aTemplate', $config['payum.template.foo']);
            },
        ]);

        $this->assertTrue($called);
    }

    public function testShouldSkipContainerServiceIfSuchNotExist(): void
    {
        $container = new Container();

        $factory = new ContainerAwareCoreGatewayFactory();
        $factory->setContainer($container);

        $called = false;

        $factory->create([
            'foo' => '@anActionService',
            'test' => function (ArrayObject $config) use (&$called): void {
                $called = true;

                $this->assertSame('@anActionService', $config['foo']);
            },
        ]);

        $this->assertTrue($called);
    }

    public function testShouldResolveContainerServiceIfSuchExist(): void
    {
        $service = new stdClass();

        $container = new Container();
        $container->set('anActionService', $service);

        $factory = new ContainerAwareCoreGatewayFactory();
        $factory->setContainer($container);

        $called = false;

        $factory->create([
            'foo' => '@anActionService',
            'test' => function (ArrayObject $config) use (&$called, $service): void {
                $called = true;

                $this->assertSame($service, $config['foo']);
            },
        ]);

        $this->assertTrue($called);
    }

    public function testShouldSkipEmptyStringValue(): void
    {
        $factory = new ContainerAwareCoreGatewayFactory();
        $factory->setContainer(new Container());

        $this->assertInstanceOf(GatewayInterface::class, $factory->create([
            'foo' => '',
        ]));
    }
}
