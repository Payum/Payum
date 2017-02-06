<?php
namespace Payum\Core\Tests\Bridge\Symfony;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Symfony\ContainerAwareCoreGatewayFactory;
use Payum\Core\CoreGatewayFactory;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class ContainerAwareCoreGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldExtendCoreGatewayFactory()
    {
        $rc = new \ReflectionClass(ContainerAwareCoreGatewayFactory::class);

        $this->assertTrue($rc->isSubclassOf(CoreGatewayFactory::class));
    }

    public function testShouldImplementContainerAwareInterface()
    {
        $rc = new \ReflectionClass(ContainerAwareCoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(ContainerAwareInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new ContainerAwareCoreGatewayFactory();
    }

    public function testShouldResolveContainerParameter()
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
            'test' => function (ArrayObject $config) use (&$called) {
                $called = true;

                $this->assertEquals('fooVal', $config['foo']);
                $this->assertEquals('barBazOloloVal', $config['bar']);
            },
        ]);

        $this->assertTrue($called);
    }

    public function testShouldResolveTemplateFromContainerParameter()
    {
        $container = new Container();
        $container->setParameter('a_template_parameter', '@aTemplate');

        $factory = new ContainerAwareCoreGatewayFactory();
        $factory->setContainer($container);

        $called = false;

        $factory->create([
            'payum.template.foo' => '%a_template_parameter%',
            'test' => function (ArrayObject $config) use (&$called) {
                $called = true;

                $this->assertEquals('@aTemplate', $config['payum.template.foo']);
            },
        ]);

        $this->assertTrue($called);
    }

    public function testShouldSkipContainerServiceIfSuchNotExist()
    {
        $container = new Container();

        $factory = new ContainerAwareCoreGatewayFactory();
        $factory->setContainer($container);

        $called = false;

        $factory->create([
            'foo' => '@anActionService',
            'test' => function (ArrayObject $config) use (&$called) {
                $called = true;

                $this->assertEquals('@anActionService', $config['foo']);
            },
        ]);

        $this->assertTrue($called);
    }

    public function testShouldResolveContainerServiceIfSuchExist()
    {
        $service = new \stdClass();

        $container = new Container();
        $container->set('anActionService', $service);

        $factory = new ContainerAwareCoreGatewayFactory();
        $factory->setContainer($container);

        $called = false;

        $factory->create([
            'foo' => '@anActionService',
            'test' => function (ArrayObject $config) use (&$called, $service) {
                $called = true;

                $this->assertSame($service, $config['foo']);
            },
        ]);

        $this->assertTrue($called);
    }

    public function testShouldSkipEmptyStringValue()
    {
        $factory = new ContainerAwareCoreGatewayFactory();
        $factory->setContainer(new Container());

        $factory->create([
            'foo' => '',
        ]);
    }
}
