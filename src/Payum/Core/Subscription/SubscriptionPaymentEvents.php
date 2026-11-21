<?php

declare(strict_types=1);

namespace Payum\Core\Subscription;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Event\SubscriptionPaymentFailed;
use Payum\Core\Event\SubscriptionPaymentSucceeded;
use Payum\Core\Handler\Context;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Model\SubscriptionPaymentInterface;
use Payum\Core\Result\Failure;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Result;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
final class SubscriptionPaymentEvents
{
    /**
     * Announces a payment of a subscription that reached a final status.
     *
     * @param CommandInterface<Result> $command
     */
    public static function dispatch(EventDispatcherInterface $events, CommandInterface $command, Context $context, SubjectInterface $payment, PaymentStatus $to, ?Failure $failure, bool $isFirst): void
    {
        $subscription = $payment instanceof SubscriptionPaymentInterface ? $payment->getSubscription() : null;

        if (! $payment instanceof PaymentInterface || ! $subscription instanceof SubscriptionInterface) {
            return;
        }

        $event = match ($to) {
            PaymentStatus::Captured, PaymentStatus::Authorized => new SubscriptionPaymentSucceeded($command, $context, $subscription, $payment, $isFirst),
            PaymentStatus::Failed => new SubscriptionPaymentFailed($command, $context, $subscription, $payment, $failure, $isFirst),
            default => null,
        };

        if (null !== $event) {
            $events->dispatch($event);
        }
    }
}
