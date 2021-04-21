<?php
namespace Payum\Core\Tests\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use PHPUnit\Framework\TestCase;

class GatewayFactoryBuilderTest extends TestCase
{
    public function testCouldBeConstructedWithGatewayFactoryClassAsFirstArgument()
    {
        new GatewayFactoryBuilder(GatewayFactory::class);
    }

    public function testShouldBuildContainerAwareCoreGatewayFactory()
    {
        /** @var GatewayFactoryInterface $coreGatewayFactory */
        $coreGatewayFactory = $this->createMock(GatewayFactoryInterface::class);
        $defaultConfig = ['foo' => 'fooVal'];

        $builder = new GatewayFactoryBuilder(GatewayFactory::class);

        $gatewayFactory = $builder->build($defaultConfig, $coreGatewayFactory);

        $this->assertInstanceOf(GatewayFactory::class, $gatewayFactory);
        $this->assertAttributeSame($coreGatewayFactory, 'coreGatewayFactory', $gatewayFactory);
        $this->assertAttributeSame($defaultConfig, 'defaultConfig', $gatewayFactory);
    }

    public function testAllowUseBuilderAsAsFunction()
    {
        /** @var GatewayFactoryInterface $coreGatewayFactory */
        $coreGatewayFactory = $this->createMock(GatewayFactoryInterface::class);
        $defaultConfig = ['foo' => 'fooVal'];

        $builder = new GatewayFactoryBuilder(GatewayFactory::class);

        $gatewayFactory = $builder($defaultConfig, $coreGatewayFactory);

        $this->assertInstanceOf(GatewayFactory::class, $gatewayFactory);
        $this->assertAttributeSame($coreGatewayFactory, 'coreGatewayFactory', $gatewayFactory);
        $this->assertAttributeSame($defaultConfig, 'defaultConfig', $gatewayFactory);
    }
}
