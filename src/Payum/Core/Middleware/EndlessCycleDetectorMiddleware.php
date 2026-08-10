<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Handler\Context;
use Payum\Core\Result\Result;
use function count;
use function sprintf;

/**
 * Stops a handler that dispatches its way into a loop.
 *
 * A handler can dispatch a sub-command, and that sub-command's handler can dispatch another. Nothing
 * stops that recursing until the process dies, so this puts a ceiling on the depth.
 */
final class EndlessCycleDetectorMiddleware implements MiddlewareInterface, HasPriority
{
    public function __construct(
        private readonly int $limit = 100
    ) {
    }

    /**
     * Outermost, so the depth is checked before anything else does work.
     */
    public static function priority(): int
    {
        return 1000;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $depth = count($context->previous());

        if ($depth >= $this->limit) {
            throw new LogicException(sprintf(
                'Possible endless cycle detected: %s was dispatched %d levels deep, which reaches the limit of %d.',
                $command::class,
                $depth,
                $this->limit,
            ));
        }

        return $next($command, $context);
    }
}
