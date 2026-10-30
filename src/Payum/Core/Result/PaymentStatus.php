<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * Replaces the GetHumanStatus / GetBinaryStatus request pair.
 *
 * The backing values match v1's GetHumanStatus::STATUS_* constants exactly, so the BC shim is a direct
 * translation and values already persisted by v1 stay valid.
 *
 * PartiallyCaptured, PartiallyRefunded, Disputed and ChargedBack are new: v1 has no way to express them.
 *
 * The set is closed. A gateway maps what its PSP reports onto these cases and never adds its own; see
 * docs/gateways/results.md for how to propose a new one.
 */
enum PaymentStatus: string
{
    case Authorized = 'authorized';

    case Canceled = 'canceled';

    case Captured = 'captured';

    /**
     * The dispute was lost and the money is gone. Distinct from Refunded, which the merchant chose.
     */
    case ChargedBack = 'charged_back';

    /**
     * The cardholder opened a dispute. The money is still with the merchant, but at risk.
     */
    case Disputed = 'disputed';

    case Expired = 'expired';

    case Failed = 'failed';

    case New = 'new';

    case PartiallyCaptured = 'partially_captured';

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
