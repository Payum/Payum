<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Event\NullEventDispatcher;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Handler\Context;
use Payum\Core\Model\PaymentStatuses;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Result;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Commits the status a handler declared onto the payment.
 *
 * A handler never touches the payment: it owns the PSP state bag and describes what happened. This is what
 * turns that description into a change, and it is the seam the state machine replaces — applying a
 * transition towards the declared status rather than assigning it, so an illegal move can be rejected.
 */
final class RecordPaymentStatusMiddleware implements MiddlewareInterface, HasPriority
{
    public function __construct(
        private readonly EventDispatcherInterface $events = new NullEventDispatcher()
    ) {
    }

    /**
     * Inside PersistStateMiddleware, so the status is set before the payment is written away. Recording it
     * further out would persist it one command late.
     */
    public static function priority(): int
    {
        return 50;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        // Deliberately not in a finally, unlike the state write outside this. An exception means we did
        // not learn what the payment's status is, and guessing would be worse than leaving it.
        $result = $next($command, $context);

        $subject = $context->subject();

        // A null status means the operation concluded nothing about the payment — a declined refund
        // leaves a captured payment captured.
        if (! $subject instanceof SubjectInterface || ! $result->status instanceof PaymentStatus) {
            return $result;
        }

        $from = PaymentStatuses::of($subject);

        // Nothing moved, so there is nothing to announce: a webhook redelivered after a capture would
        // otherwise fulfil the order twice.
        if (PaymentStatuses::set($subject, $result->status) && $from !== $result->status) {
            $this->events->dispatch(new StatusChanged($command, $context, $subject, $from, $result->status));
        }

        return $result;
    }
}
