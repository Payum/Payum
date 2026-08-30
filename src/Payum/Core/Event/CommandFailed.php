<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\Result;
use Throwable;

/**
 * An exception escaped the command, so there is no result.
 *
 * A fault rather than a decline: an unreachable PSP, credentials that no longer work, a webhook whose
 * signature did not check out. The exception is rethrown after this is dispatched, so a listener sees it
 * before the caller does but cannot swallow it.
 *
 * The payment's status is deliberately not touched on this path -- an exception means nobody learned
 * what it is -- so no {@see StatusChanged} accompanies this.
 */
final class CommandFailed extends Event
{
    /**
     * @param CommandInterface<Result> $command
     */
    public function __construct(
        CommandInterface $command,
        Context $context,
        public readonly Throwable $exception,
    ) {
        parent::__construct($command, $context);
    }
}
