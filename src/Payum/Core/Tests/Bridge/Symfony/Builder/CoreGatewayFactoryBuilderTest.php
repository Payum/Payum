<?php
namespace Payum\Core\Tests\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Builder\CoreGatewayFactoryBuilder;
use Payum\Core\Bridge\Symfony\ContainerAwareCoreGatewayFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class CoreGatewayFactoryBuilderTest extends TestCase
{
    public function testShouldImplementContainerAwareInterface()
    {
        $rc = new \ReflectionClass(CoreGatewayFactoryBuilder::class);

        $this->assertTrue($rc->implementsInterface(ContainerAwareInterface::class));
    }

    public function testShouldBuildContainerAwareCoreGatewayFactory()
    {
        $container = new Container();
        $defaultConfig = ['foo' => 'fooVal'];

        $builder = new CoreGatewayFactoryBuilder();
        $builder->setContainer($container);

        $gatewayFactory = $builder->build($defaultConfig);

        $this->assertInstanceOf(ContainerAwareCoreGatewayFactory::class, $gatewayFactory);
    }

    public function testAllowUseBuilderAsAsFunction()
    {
        $container = new Container();
        $defaultConfig = ['foo' => 'fooVal'];

        $builder = new CoreGatewayFactoryBuilder();
        $builder->setContainer($container);

        $gatewayFactory = $builder($defaultConfig);

        $this->assertInstanceOf(ContainerAwareCoreGatewayFactory::class, $gatewayFactory);
    }
}
