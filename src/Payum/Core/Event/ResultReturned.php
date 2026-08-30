<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\Result;

/**
 * The command finished and its result is on its way back to the caller.
 *
 * Dispatched for every result, declined ones included -- {@see FailureRaised} follows for those. The
 * state the handler produced has already been written to the payment by the time this fires, so a
 * listener reading the subject sees the payment as the command left it.
 */
final class ResultReturned extends Event
{
    /**
     * @param CommandInterface<Result> $command
     */
    public function __construct(
        CommandInterface $command,
        Context $context,
        public readonly Result $result,
    ) {
        parent::__construct($command, $context);
    }
}
