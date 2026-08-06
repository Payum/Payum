<?php

namespace Payum\Core\Tests\DI;

use DI\Container;
use DI\ContainerBuilder;
use Payum\Core\DI\FallbackContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use stdClass;

class FallbackContainerTest extends TestCase
{
    public function testShouldImplementContainerInterface(): void
    {
        $rc = new ReflectionClass(FallbackContainer::class);

        $this->assertTrue($rc->implementsInterface(ContainerInterface::class));
    }

    public function testShouldBeFinal(): void
    {
        $rc = new ReflectionClass(FallbackContainer::class);

        $this->assertTrue($rc->isFinal());
    }

    public function testShouldReturnTheServiceOfThePrimaryContainer(): void
    {
        $expected = new stdClass();

        $container = new FallbackContainer(
            $this->buildContainer([
                'foo' => $expected,
            ]),
            $this->buildContainer([
                'foo' => new stdClass(),
            ])
        );

        $this->assertSame($expected, $container->get('foo'));
    }

    public function testShouldFallBackWhenThePrimaryContainerDoesNotHaveTheService(): void
    {
        $expected = new stdClass();

        $container = new FallbackContainer(
            $this->buildContainer(),
            $this->buildContainer([
                'foo' => $expected,
            ])
        );

        $this->assertSame($expected, $container->get('foo'));
    }

    public function testShouldNotTouchTheFallbackWhenThePrimaryContainerHasTheService(): void
    {
        $fallback = $this->createMock(ContainerInterface::class);
        $fallback->expects($this->never())->method('get');

        $container = new FallbackContainer(
            $this->buildContainer([
                'foo' => 'fooVal',
            ]),
            $fallback
        );

        $this->assertSame('fooVal', $container->get('foo'));
    }

    public function testHasShouldBeTrueWhenEitherContainerHasTheService(): void
    {
        $container = new FallbackContainer(
            $this->buildContainer([
                'foo' => 'fooVal',
            ]),
            $this->buildContainer([
                'bar' => 'barVal',
            ])
        );

        $this->assertTrue($container->has('foo'));
        $this->assertTrue($container->has('bar'));
    }

    public function testHasShouldBeFalseWhenNeitherContainerHasTheService(): void
    {
        $container = new FallbackContainer($this->buildContainer(), $this->buildContainer());

        $this->assertFalse($container->has('an.unknown.service'));
    }

    public function testShouldThrowNotFoundWhenNeitherContainerHasTheService(): void
    {
        $container = new FallbackContainer($this->buildContainer(), $this->buildContainer());

        $this->expectException(NotFoundExceptionInterface::class);

        $container->get('an.unknown.service');
    }

    public function testShouldReportTheKnownEntryNamesOfBothContainers(): void
    {
        $container = new FallbackContainer(
            $this->buildContainer([
                'foo' => 'fooVal',
            ]),
            $this->buildContainer([
                'bar' => 'barVal',
            ])
        );

        $names = $container->getKnownEntryNames();

        $this->assertContains('foo', $names);
        $this->assertContains('bar', $names);
    }

    public function testShouldReportEachKnownEntryNameOnlyOnce(): void
    {
        $container = new FallbackContainer(
            $this->buildContainer([
                'foo' => 'fooVal',
            ]),
            $this->buildContainer([
                'foo' => 'anotherFooVal',
            ])
        );

        $names = $container->getKnownEntryNames();

        $this->assertSame(1, array_count_values($names)['foo']);
    }

    public function testShouldReportNothingForAContainerWhichCannotEnumerateItsEntries(): void
    {
        $container = new FallbackContainer(
            $this->createMock(ContainerInterface::class),
            $this->createMock(ContainerInterface::class)
        );

        $this->assertSame([], $container->getKnownEntryNames());
    }

    public function testShouldReportTheKnownEntryNamesOfTheEnumerableSideOnly(): void
    {
        $container = new FallbackContainer(
            $this->createMock(ContainerInterface::class),
            $this->buildContainer([
                'bar' => 'barVal',
            ])
        );

        $this->assertContains('bar', $container->getKnownEntryNames());
    }

    public function testShouldReportTheKnownEntryNamesOfANestedFallbackContainer(): void
    {
        $container = new FallbackContainer(
            $this->buildContainer([
                'foo' => 'fooVal',
            ]),
            new FallbackContainer(
                $this->buildContainer([
                    'bar' => 'barVal',
                ]),
                $this->buildContainer([
                    'baz' => 'bazVal',
                ])
            )
        );

        $names = $container->getKnownEntryNames();

        $this->assertContains('foo', $names);
        $this->assertContains('bar', $names);
        $this->assertContains('baz', $names);
    }

    public function testShouldResolveThroughANestedFallbackContainer(): void
    {
        $expected = new stdClass();

        $container = new FallbackContainer(
            $this->buildContainer(),
            new FallbackContainer(
                $this->buildContainer(),
                $this->buildContainer([
                    'foo' => $expected,
                ])
            )
        );

        $this->assertTrue($container->has('foo'));
        $this->assertSame($expected, $container->get('foo'));
    }

    /**
     * @param array<string, mixed> $definitions
     */
    private function buildContainer(array $definitions = []): Container
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions($definitions);

        return $builder->build();
    }
}
