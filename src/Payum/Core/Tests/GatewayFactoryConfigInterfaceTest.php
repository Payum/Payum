<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use DI\Container;
use Payum\Core\Action\ActionInterface;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactoryConfigInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;

final class GatewayFactoryConfigInterfaceTest extends TestCase
{
    public function testShouldBeInterface(): void
    {
        $rc = new ReflectionClass(GatewayFactoryConfigInterface::class);

        $this->assertTrue($rc->isInterface());
    }

    public function testShouldDefineCreateGatewayMethodTakingContainerAndReturningGateway(): void
    {
        $rc = new ReflectionClass(GatewayFactoryConfigInterface::class);

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

    public function testShouldDefineGetActionsMethodReturningArray(): void
    {
        $rc = new ReflectionClass(GatewayFactoryConfigInterface::class);

        $this->assertTrue($rc->hasMethod('getActions'));

        $method = $rc->getMethod('getActions');

        $this->assertTrue($method->isPublic());
        $this->assertSame(0, $method->getNumberOfParameters());

        $returnType = $method->getReturnType();

        $this->assertInstanceOf(ReflectionNamedType::class, $returnType);
        $this->assertSame('array', $returnType->getName());
    }

    public function testShouldDefineGetExtensionsMethodReturningArray(): void
    {
        $rc = new ReflectionClass(GatewayFactoryConfigInterface::class);

        $this->assertTrue($rc->hasMethod('getExtensions'));

        $method = $rc->getMethod('getExtensions');

        $this->assertTrue($method->isPublic());
        $this->assertSame(0, $method->getNumberOfParameters());

        $returnType = $method->getReturnType();

        $this->assertInstanceOf(ReflectionNamedType::class, $returnType);
        $this->assertSame('array', $returnType->getName());
    }

    public function testShouldBeImplementedByCoreGatewayFactory(): void
    {
        $rc = new ReflectionClass(CoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryConfigInterface::class));
    }

    public function testShouldAllowCustomImplementation(): void
    {
        $extension = new GatewayFactoryConfigStubExtension();

        $config = new class($extension) implements GatewayFactoryConfigInterface {
            public function __construct(
                private ExtensionInterface $extension
            ) {
            }

            public function createGateway(ContainerInterface $container): Gateway
            {
                $gateway = new Gateway();

                foreach ($this->getActions() as $action) {
                    $gateway->addAction($container->get($action));
                }

                foreach ($this->getExtensions() as $extension) {
                    $gateway->addExtension($extension);
                }

                return $gateway;
            }

            public function getActions(): array
            {
                return [GatewayFactoryConfigStubAction::class];
            }

            public function getExtensions(): array
            {
                return [$this->extension];
            }
        };

        $this->assertSame([GatewayFactoryConfigStubAction::class], $config->getActions());
        $this->assertSame([$extension], $config->getExtensions());

        $gateway = $config->createGateway(new Container());

        $this->assertInstanceOf(Gateway::class, $gateway);
        $this->assertSame([GatewayFactoryConfigStubAction::class], array_map(
            get_class(...),
            self::readAttribute($gateway, 'actions')
        ));
        $this->assertSame(
            [$extension],
            self::readAttribute(self::readAttribute($gateway, 'extensions'), 'extensions')
        );
    }
}

class GatewayFactoryConfigStubAction implements ActionInterface
{
    public function execute($request): void
    {
    }

    public function supports($request): bool
    {
        return false;
    }
}

class GatewayFactoryConfigStubExtension implements ExtensionInterface
{
    public function onPreExecute(Context $context): void
    {
    }

    public function onExecute(Context $context): void
    {
    }

    public function onPostExecute(Context $context): void
    {
    }
}
