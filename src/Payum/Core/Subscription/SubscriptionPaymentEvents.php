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
     * Announces a payment of a subscription that succeeded or failed.
     *
     * @param CommandInterface<Result> $command
     * @param PaymentStatus|null $from an authorization already announced the charge it is later captured for
     */
    public static function dispatch(EventDispatcherInterface $events, CommandInterface $command, Context $context, SubjectInterface $payment, ?PaymentStatus $from, PaymentStatus $to, ?Failure $failure, bool $isFirst): void
    {
        $subscription = $payment instanceof SubscriptionPaymentInterface ? $payment->getSubscription() : null;

        if (! $payment instanceof PaymentInterface || ! $subscription instanceof SubscriptionInterface) {
            return;
        }

        $succeededBefore = in_array($from, [PaymentStatus::Authorized, PaymentStatus::Captured], true);

        $event = match (true) {
            ! $succeededBefore && in_array($to, [PaymentStatus::Captured, PaymentStatus::Authorized], true) => new SubscriptionPaymentSucceeded($command, $context, $subscription, $payment, $isFirst),
            PaymentStatus::Failed === $to => new SubscriptionPaymentFailed($command, $context, $subscription, $payment, $failure, $isFirst),
            default => null,
        };

        if (null !== $event) {
            $events->dispatch($event);
        }
    }
}
