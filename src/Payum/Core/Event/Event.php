<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\Result;

/**
 * Something that happened while a command was executing.
 *
 * Listeners bind to the concrete subclass. This base exists for the listener that wants everything --
 * an audit log, a debug toolbar -- so that it has one type to bind to.
 *
 * An event is a notification, not a hook. Core ignores whatever a listener returns, and a listener that
 * throws takes the command down with it. Changing what happens is what middleware is for.
 */
abstract class Event
{
    /**
     * @param CommandInterface<Result> $command
     */
    public function __construct(
        public readonly CommandInterface $command,
        public readonly Context $context,
    ) {
    }
}
