<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Result\PaymentStatus;

/**
 * Marks a 1.x status request from a recorded status.
 *
 * @deprecated since 2.0, removed in 3.0 along with the requests it marks.
 */
final class StatusMarker
{
    /**
     * A null status means the subject does not track one, so nobody knows -- which is what markUnknown
     * means, and is honest rather than guessing at New.
     *
     * PartiallyRefunded has no 1.x equivalent and becomes refunded, which is what 1.x would have said
     * about the same payment.
     */
    public static function mark(GetStatusInterface $request, ?PaymentStatus $status): void
    {
        match ($status) {
            PaymentStatus::New => $request->markNew(),
            PaymentStatus::Pending => $request->markPending(),
            PaymentStatus::Authorized => $request->markAuthorized(),
            PaymentStatus::Captured => $request->markCaptured(),
            PaymentStatus::Refunded, PaymentStatus::PartiallyRefunded => $request->markRefunded(),
            PaymentStatus::PaidOut => $request->markPayedout(),
            PaymentStatus::Canceled => $request->markCanceled(),
            PaymentStatus::Failed => $request->markFailed(),
            PaymentStatus::Expired => $request->markExpired(),
            PaymentStatus::Suspended => $request->markSuspended(),
            default => $request->markUnknown(),
        };
    }
}
