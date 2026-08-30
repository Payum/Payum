<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Result;

/**
 * A payment moved from one status to another.
 *
 * Dispatched only when the status actually changed, so a second capture of an already-captured payment
 * is silent. A payment that tracks no status never gets here, and neither does a command that concluded
 * nothing about the one it operated on -- a declined refund leaves a captured payment captured.
 *
 * This is what a notification or a fulfilment listener wants: it fires wherever the change came from,
 * including a webhook nobody was waiting for.
 */
final class StatusChanged extends Event
{
    /**
     * @param CommandInterface<Result> $command
     * @param PaymentStatus|null $from null when the payment had no status recorded yet
     */
    public function __construct(
        CommandInterface $command,
        Context $context,
        public readonly SubjectInterface $subject,
        public readonly ?PaymentStatus $from,
        public readonly PaymentStatus $to,
    ) {
        parent::__construct($command, $context);
    }
}
