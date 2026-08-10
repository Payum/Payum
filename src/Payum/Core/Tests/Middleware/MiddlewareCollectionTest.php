<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use DI\Container;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\HasPriority;
use Payum\Core\Middleware\MiddlewareCollection;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Result;
use PHPUnit\Framework\TestCase;

final class MiddlewareCollectionTest extends TestCase
{
    public function testShouldStartEmpty(): void
    {
        $collection = new MiddlewareCollection();

        $this->assertTrue($collection->isEmpty());
        $this->assertSame([], $collection->resolve(new Container()));
    }

    public function testShouldNotMutateWhenAddingTo(): void
    {
        $collection = new MiddlewareCollection();
        $with = $collection->with(new LowMiddleware());

        $this->assertTrue($collection->isEmpty());
        $this->assertFalse($with->isEmpty());
    }

    public function testShouldOrderByPriorityHighestFirst(): void
    {
        $high = new HighMiddleware();
        $low = new LowMiddleware();

        $resolved = (new MiddlewareCollection())
            ->with($low)
            ->with($high)
            ->resolve(new Container());

        $this->assertSame([$high, $low], $resolved);
    }

    public function testShouldTakeThePriorityAMiddlewareDeclares(): void
    {
        $declared = new HighMiddleware();
        $undeclared = new PlainMiddleware();

        $resolved = (new MiddlewareCollection())
            ->with($undeclared)
            ->with($declared)
            ->resolve(new Container());

        // HighMiddleware declares 100 through HasPriority; PlainMiddleware defaults to 0.
        $this->assertSame([$declared, $undeclared], $resolved);
    }

    public function testShouldLetAnExplicitPriorityOverrideTheDeclaredOne(): void
    {
        $declared = new HighMiddleware();
        $plain = new PlainMiddleware();

        $resolved = (new MiddlewareCollection())
            ->with($plain, 500)
            ->with($declared)
            ->resolve(new Container());

        $this->assertSame([$plain, $declared], $resolved);
    }

    public function testShouldKeepRegistrationOrderOnEqualPriority(): void
    {
        $first = new PlainMiddleware();
        $second = new PlainMiddleware();
        $third = new PlainMiddleware();

        $resolved = (new MiddlewareCollection())
            ->with($first)
            ->with($second)
            ->with($third)
            ->resolve(new Container());

        $this->assertSame([$first, $second, $third], $resolved);
    }

    public function testShouldResolveAContainerIdToItsService(): void
    {
        $middleware = new PlainMiddleware();
        $container = new Container([
            PlainMiddleware::class => $middleware,
        ]);

        $resolved = (new MiddlewareCollection())->with(PlainMiddleware::class)->resolve($container);

        $this->assertSame([$middleware], $resolved);
    }

    public function testShouldReadThePriorityOfAMiddlewareRegisteredByIdWithoutBuildingIt(): void
    {
        $container = new Container([
            HighMiddleware::class => new HighMiddleware(),
            PlainMiddleware::class => new PlainMiddleware(),
        ]);

        $resolved = (new MiddlewareCollection())
            ->with(PlainMiddleware::class)
            ->with(HighMiddleware::class)
            ->resolve($container);

        $this->assertInstanceOf(HighMiddleware::class, $resolved[0]);
        $this->assertInstanceOf(PlainMiddleware::class, $resolved[1]);
    }

    public function testShouldAppendWhatIsMergedIn(): void
    {
        $first = new PlainMiddleware();
        $second = new PlainMiddleware();

        $resolved = (new MiddlewareCollection())
            ->with($first)
            ->merge((new MiddlewareCollection())->with($second))
            ->resolve(new Container());

        $this->assertSame([$first, $second], $resolved);
    }

    public function testShouldThrowWhenAnEntryIsNotMiddleware(): void
    {
        $container = new Container([
            'not_middleware' => new \stdClass(),
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(MiddlewareInterface::class);

        /** @phpstan-ignore argument.type */
        (new MiddlewareCollection())->with('not_middleware')->resolve($container);
    }
}

final class PlainMiddleware implements MiddlewareInterface
{
    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        return CaptureResult::captured();
    }
}

final class HighMiddleware implements MiddlewareInterface, HasPriority
{
    public static function priority(): int
    {
        return 100;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        return CaptureResult::captured();
    }
}

final class LowMiddleware implements MiddlewareInterface, HasPriority
{
    public static function priority(): int
    {
        return -100;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        return CaptureResult::captured();
    }
}
