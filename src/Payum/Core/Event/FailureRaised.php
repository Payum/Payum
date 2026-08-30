<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\Failure;
use Payum\Core\Result\Result;

/**
 * The PSP said no: a decline, a rejected card, an authentication the customer abandoned.
 *
 * Follows the {@see ResultReturned} carrying the same result, so a listener that wants only the bad news
 * binds here instead of inspecting every result. The counterpart for a fault -- an unreachable host,
 * broken credentials -- is {@see CommandFailed}, because declines are results and faults are exceptions.
 */
final class FailureRaised extends Event
{
    /**
     * @param CommandInterface<Result> $command
     */
    public function __construct(
        CommandInterface $command,
        Context $context,
        public readonly Failure $failure,
        public readonly Result $result,
    ) {
        parent::__construct($command, $context);
    }
}
