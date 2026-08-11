<?php

declare(strict_types=1);

namespace Payum\Core\Tests\DI;

use Payum\Core\DI\FallbackContainer;
use Payum\Core\DI\ListableContainerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;

final class ListableContainerInterfaceTest extends TestCase
{
    public function testShouldBeInterface(): void
    {
        $rc = new ReflectionClass(ListableContainerInterface::class);

        $this->assertTrue($rc->isInterface());
    }

    public function testShouldExtendContainerInterface(): void
    {
        $rc = new ReflectionClass(ListableContainerInterface::class);

        $this->assertTrue($rc->implementsInterface(ContainerInterface::class));
    }

    public function testShouldDefineGetKnownEntryNamesMethodReturningArray(): void
    {
        $rc = new ReflectionClass(ListableContainerInterface::class);

        $this->assertTrue($rc->hasMethod('getKnownEntryNames'));

        $method = $rc->getMethod('getKnownEntryNames');

        $this->assertTrue($method->isPublic());
        $this->assertSame(0, $method->getNumberOfParameters());

        $returnType = $method->getReturnType();

        $this->assertInstanceOf(ReflectionNamedType::class, $returnType);
        $this->assertSame('array', $returnType->getName());
    }

    public function testShouldBeImplementedByFallbackContainer(): void
    {
        $rc = new ReflectionClass(FallbackContainer::class);

        $this->assertTrue($rc->implementsInterface(ListableContainerInterface::class));
    }

    public function testShouldAllowCustomImplementation(): void
    {
        $container = new class() implements ListableContainerInterface {
            public function get(string $id): mixed
            {
                return 'fooVal';
            }

            public function has(string $id): bool
            {
                return 'foo' === $id;
            }

            public function getKnownEntryNames(): array
            {
                return ['foo'];
            }
        };

        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertSame(['foo'], $container->getKnownEntryNames());
    }
}
