<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Result\Failure;
use Payum\Core\Result\Result;

/**
 * A payment of a subscription failed. Redelivering the same charge is silent.
 */
final class SubscriptionPaymentFailed extends Event
{
    /**
     * @param CommandInterface<Result> $command
     * @param bool $isFirst the charge that started the subscription rather than a renewal of it
     */
    public function __construct(
        CommandInterface $command,
        Context $context,
        public readonly SubscriptionInterface $subscription,
        public readonly PaymentInterface $payment,
        public readonly ?Failure $failure,
        public readonly bool $isFirst,
    ) {
        parent::__construct($command, $context);
    }
}
