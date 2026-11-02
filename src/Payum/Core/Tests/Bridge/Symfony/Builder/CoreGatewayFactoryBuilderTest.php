<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Builder\CoreGatewayFactoryBuilder;
use Payum\Core\Bridge\Symfony\ContainerAwareCoreGatewayFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

final class CoreGatewayFactoryBuilderTest extends TestCase
{
    public function testShouldBuildContainerAwareCoreGatewayFactory(): void
    {
        $container = new Container();
        $defaultConfig = [
            'foo' => 'fooVal',
        ];

        $builder = new CoreGatewayFactoryBuilder();
        $builder->setContainer($container);

        $gatewayFactory = $builder->build($defaultConfig);

        $this->assertInstanceOf(ContainerAwareCoreGatewayFactory::class, $gatewayFactory);
    }

    public function testAllowUseBuilderAsAsFunction(): void
    {
        $container = new Container();
        $defaultConfig = [
            'foo' => 'fooVal',
        ];

        $builder = new CoreGatewayFactoryBuilder();
        $builder->setContainer($container);

        $gatewayFactory = $builder($defaultConfig);

        $this->assertInstanceOf(ContainerAwareCoreGatewayFactory::class, $gatewayFactory);
    }
}
