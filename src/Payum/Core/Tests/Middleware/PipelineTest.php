<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Gateway;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Model\Payment;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Result;
use Payum\Core\Security\GenericTokenFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

final class PipelineTest extends TestCase
{
    public function testShouldCallTheHandlerWhenThereIsNoMiddleware(): void
    {
        $result = (new Pipeline())->process(
            $this->command(),
            $this->context(),
            static fn (): CaptureResult => CaptureResult::captured('txn_1'),
        );

        $this->assertSame('txn_1', $result->transactionId);
    }

    public function testShouldRunMiddlewareOutermostFirstAndUnwindInReverse(): void
    {
        $log = new CallLog();

        (new Pipeline([
            new SpyMiddleware('outer', $log),
            new SpyMiddleware('inner', $log),
        ]))->process($this->command(), $this->context(), static function () use ($log): CaptureResult {
            $log->calls[] = 'handler';

            return CaptureResult::captured();
        });

        $this->assertSame(
            ['outer:before', 'inner:before', 'handler', 'inner:after', 'outer:after'],
            $log->calls,
        );
    }

    public function testShouldLetMiddlewareShortCircuitTheHandler(): void
    {
        $reached = false;

        $result = (new Pipeline([new ShortCircuitMiddleware()]))->process(
            $this->command(),
            $this->context(),
            static function () use (&$reached): CaptureResult {
                $reached = true;

                return CaptureResult::captured('from_handler');
            },
        );

        $this->assertFalse($reached);
        $this->assertSame('short_circuited', $result->transactionId);
    }

    public function testShouldLetMiddlewareReplaceTheCommandPassedOn(): void
    {
        $seen = null;

        (new Pipeline([new ReplaceCommandMiddleware()]))->process(
            $this->command(),
            $this->context(),
            static function (CommandInterface $command) use (&$seen): CaptureResult {
                $seen = $command;

                return CaptureResult::captured();
            },
        );

        $this->assertInstanceOf(CaptureCommand::class, $seen);
        $this->assertSame(999, $seen->amount);
    }

    public function testShouldLetMiddlewareSeeAnExceptionFromFurtherIn(): void
    {
        $middleware = new RecordingFailureMiddleware();

        try {
            (new Pipeline([$middleware]))->process(
                $this->command(),
                $this->context(),
                static fn () => throw new RuntimeException('psp exploded'),
            );

            $this->fail('Expected the exception to propagate.');
        } catch (RuntimeException $e) {
            $this->assertSame('psp exploded', $e->getMessage());
            $this->assertSame('psp exploded', $middleware->seen?->getMessage());
        }
    }

    private function command(): CaptureCommand
    {
        return CaptureCommand::forPayment(new Payment());
    }

    private function context(): Context
    {
        return new Context(
            $this->createMock(Gateway::class),
            $this->command(),
            $this->createMock(PaymentGateway::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(GenericTokenFactoryInterface::class),
        );
    }
}

final class CallLog
{
    /**
     * @var list<string>
     */
    public array $calls = [];
}

final class SpyMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $name,
        private readonly CallLog $log
    ) {
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $this->log->calls[] = $this->name . ':before';

        $result = $next($command, $context);

        $this->log->calls[] = $this->name . ':after';

        return $result;
    }
}

final class ShortCircuitMiddleware implements MiddlewareInterface
{
    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        return CaptureResult::captured('short_circuited');
    }
}

final class ReplaceCommandMiddleware implements MiddlewareInterface
{
    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        return $next(CaptureCommand::forPayment(new Payment(), 999), $context);
    }
}

final class RecordingFailureMiddleware implements MiddlewareInterface
{
    public ?Throwable $seen = null;

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        try {
            return $next($command, $context);
        } catch (Throwable $e) {
            $this->seen = $e;

            throw $e;
        }
    }
}
