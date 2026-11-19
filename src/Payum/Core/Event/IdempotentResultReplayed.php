<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\Result;

/**
 * A repeat of an idempotency key was answered with the result the key got the first time. No handler
 * ran, so neither {@see HandlerResolved} nor {@see StatusChanged} fires for this command.
 */
final class IdempotentResultReplayed extends Event
{
    /**
     * @param CommandInterface<Result> $command
     */
    public function __construct(
        CommandInterface $command,
        Context $context,
        public readonly string $idempotencyKey,
        public readonly Result $result,
    ) {
        parent::__construct($command, $context);
    }
}
