<?php

declare(strict_types=1);

namespace Payum\Core\Tests\DI;

use DI\Container;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Gateway;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;

final class ContainerConfigurationTest extends TestCase
{
    public function testShouldBeInterface(): void
    {
        $rc = new ReflectionClass(ContainerConfiguration::class);

        $this->assertTrue($rc->isInterface());
    }

    public function testShouldDefineConfigureContainerMethodReturningArray(): void
    {
        $rc = new ReflectionClass(ContainerConfiguration::class);

        $this->assertTrue($rc->hasMethod('configureContainer'));

        $method = $rc->getMethod('configureContainer');

        $this->assertTrue($method->isPublic());
        $this->assertSame(0, $method->getNumberOfParameters());

        $returnType = $method->getReturnType();

        $this->assertInstanceOf(ReflectionNamedType::class, $returnType);
        $this->assertSame('array', $returnType->getName());
    }

    public function testShouldDefineCreateGatewayMethodTakingContainerAndReturningGateway(): void
    {
        $rc = new ReflectionClass(ContainerConfiguration::class);

        $this->assertTrue($rc->hasMethod('createGateway'));

        $method = $rc->getMethod('createGateway');

        $this->assertTrue($method->isPublic());
        $this->assertSame(1, $method->getNumberOfParameters());

        $parameterType = $method->getParameters()[0]->getType();

        $this->assertInstanceOf(ReflectionNamedType::class, $parameterType);
        $this->assertSame(ContainerInterface::class, $parameterType->getName());

        $returnType = $method->getReturnType();

        $this->assertInstanceOf(ReflectionNamedType::class, $returnType);
        $this->assertSame(Gateway::class, $returnType->getName());
    }

    public function testShouldBeImplementedByCoreGatewayFactory(): void
    {
        $rc = new ReflectionClass(CoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(ContainerConfiguration::class));
    }

    public function testShouldAllowCustomImplementation(): void
    {
        $configuration = new class() implements ContainerConfiguration {
            public function configureContainer(): array
            {
                return [
                    'foo' => 'fooVal',
                ];
            }

            public function createGateway(ContainerInterface $container): Gateway
            {
                return new Gateway();
            }
        };

        $this->assertInstanceOf(ContainerConfiguration::class, $configuration);
        $this->assertSame([
            'foo' => 'fooVal',
        ], $configuration->configureContainer());
        $this->assertInstanceOf(Gateway::class, $configuration->createGateway(new Container()));
    }
}
