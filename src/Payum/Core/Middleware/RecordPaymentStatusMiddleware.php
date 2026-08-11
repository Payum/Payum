<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\PaymentStatuses;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Result;

/**
 * Commits the status a handler declared onto the payment.
 *
 * A handler never touches the payment: it owns the PSP state bag and describes what happened. This is what
 * turns that description into a change, and it is the seam the state machine replaces — applying a
 * transition towards the declared status rather than assigning it, so an illegal move can be rejected.
 */
final class RecordPaymentStatusMiddleware implements MiddlewareInterface, HasPriority
{
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

        $payment = $context->payment();

        // A null status means the operation concluded nothing about the payment — a declined refund
        // leaves a captured payment captured.
        if ($payment instanceof PaymentInterface && $result->status instanceof PaymentStatus) {
            PaymentStatuses::set($payment, $result->status);
        }

        return $result;
    }
}
