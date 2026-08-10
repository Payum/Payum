<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * Replaces the GetHumanStatus / GetBinaryStatus request pair.
 *
 * The backing values match v1's GetHumanStatus::STATUS_* constants exactly, so the BC shim is a direct
 * translation and values already persisted by v1 stay valid.
 *
 * PartiallyRefunded is the one genuinely new state -- v1 has no way to express it.
 */
enum PaymentStatus: string
{
    case Authorized = 'authorized';

    case Canceled = 'canceled';

    case Captured = 'captured';

    case Expired = 'expired';

    case Failed = 'failed';

    case New = 'new';

    case PartiallyRefunded = 'partially_refunded';

    /**
     * v1 spells this 'payedout'. The backing value is kept for stored data; the case name is corrected.
     */
    case PaidOut = 'payedout';

    case Pending = 'pending';

    case Refunded = 'refunded';

    case Suspended = 'suspended';

    case Unknown = 'unknown';
}
