<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use Payum\Core\Result\PaymentStatus;

/**
 * Reads and writes the status of a payment, whether or not it tracks one.
 *
 * The single place that knows how a payment's status is stored. When the state machine arrives, reading a
 * status becomes reading a workflow marking, and this is the seam where that changes.
 */
final class PaymentStatuses
{
    /**
     * Null when the payment does not track a status, which is not the same as {@see PaymentStatus::New} —
     * it means nobody knows, not that nothing has happened.
     */
    public static function of(object $payment): ?PaymentStatus
    {
        return $payment instanceof StatusAwareInterface ? $payment->getStatus() : null;
    }

    /**
     * @return bool whether the payment tracks a status and was therefore updated
     */
    public static function set(object $payment, PaymentStatus $status): bool
    {
        if (! $payment instanceof StatusAwareInterface) {
            return false;
        }

        $payment->setStatus($status);

        return true;
    }

    public static function isTracked(object $payment): bool
    {
        return $payment instanceof StatusAwareInterface;
    }
}
