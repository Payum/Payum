<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Result\Result;
use Payum\Core\Result\SubscriptionStatus;

/**
 * A subscription moved from one status to another. Redelivering the same details is silent.
 */
final class SubscriptionStatusChanged extends Event
{
    /**
     * @param CommandInterface<Result> $command
     */
    public function __construct(
        CommandInterface $command,
        Context $context,
        public readonly SubscriptionInterface $subscription,
        public readonly ?SubscriptionStatus $from,
        public readonly SubscriptionStatus $to,
    ) {
        parent::__construct($command, $context);
    }
}
