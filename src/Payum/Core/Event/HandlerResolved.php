<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Result\Result;

/**
 * The container produced the handler that will answer the command.
 *
 * Dispatched from inside the pipeline, so every middleware has already run its way in. A command that a
 * guard middleware short-circuits never gets here, which is the difference between this and
 * {@see CommandDispatched}.
 */
final class HandlerResolved extends Event
{
    /**
     * @param CommandInterface<Result> $command
     */
    public function __construct(
        CommandInterface $command,
        Context $context,
        public readonly HandlerInterface $handler,
    ) {
        parent::__construct($command, $context);
    }
}
