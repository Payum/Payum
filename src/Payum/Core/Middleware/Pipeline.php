<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\Result;
use function array_reverse;

/**
 * Runs a command through the middleware and into the handler.
 *
 * Built once per gateway and reused, so it holds no per-execution state. Nesting works because a
 * sub-command dispatched from a handler goes back through the same pipeline.
 */
final class Pipeline
{
    /**
     * @param list<MiddlewareInterface> $middleware outermost first
     */
    public function __construct(
        private readonly array $middleware = []
    ) {
    }

    /**
     * @param CommandInterface<Result> $command
     * @param callable(CommandInterface<Result>, Context): Result $handler
     */
    public function process(CommandInterface $command, Context $context, callable $handler): Result
    {
        $next = $handler;

        // Wrapped from the inside out, so the first entry ends up outermost.
        foreach (array_reverse($this->middleware) as $middleware) {
            $next = static fn (CommandInterface $c, Context $ctx): Result => $middleware->process($c, $ctx, $next);
        }

        return $next($command, $context);
    }
}
