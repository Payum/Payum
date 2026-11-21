<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Handler\Context;
use Payum\Core\Model\PaymentStatuses;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\Failure;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Result;
use Payum\Core\Subscription\SubscriptionPaymentEvents;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * A payment's status moved when a declared one was recorded onto it.
 *
 * @internal
 */
final class PaymentStatusChange
{
    private function __construct(
        public readonly SubjectInterface $payment,
        public readonly ?PaymentStatus $from,
        public readonly PaymentStatus $to,
    ) {
    }

    /**
     * Sets the status, and returns the change, or null when nothing moved: a webhook redelivered after a
     * capture would otherwise fulfil the order twice.
     */
    public static function record(SubjectInterface $payment, PaymentStatus $to): ?self
    {
        $from = PaymentStatuses::of($payment);

        return PaymentStatuses::set($payment, $to) && $from !== $to ? new self($payment, $from, $to) : null;
    }

    /**
     * @param CommandInterface<Result> $command
     * @param bool $isFirst the charge that started its subscription, for a payment that has one
     */
    public function announce(EventDispatcherInterface $events, CommandInterface $command, Context $context, ?Failure $failure, bool $isFirst): void
    {
        $events->dispatch(new StatusChanged($command, $context, $this->payment, $this->from, $this->to));
        SubscriptionPaymentEvents::dispatch($events, $command, $context, $this->payment, $this->to, $failure, $isFirst);
    }
}
