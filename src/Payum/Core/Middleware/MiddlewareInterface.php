<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\Result;

/**
 * Wraps the execution of a command.
 *
 * Middleware runs around the handler, not instead of it: call $next to continue, and what you do before
 * and after that call is your concern. Returning without calling $next short-circuits the command, which
 * is how a cache or a guard would work.
 */
interface MiddlewareInterface
{
    /**
     * @param CommandInterface<Result> $command
     * @param callable(CommandInterface<Result>, Context): Result $next
     */
    public function process(CommandInterface $command, Context $context, callable $next): Result;
}
