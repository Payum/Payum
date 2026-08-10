<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * A small closed taxonomy every gateway maps its own error codes onto.
 *
 * v1 has no portable way to answer "was this declined, and can I retry?". This is what makes portable
 * retry and fallback logic, and useful merchant-facing messages, possible. It is nearly impossible to add
 * later because it needs every gateway to participate.
 *
 * The raw PSP code is always preserved alongside, on {@see Failure}.
 */
enum FailureReason: string
{
    case AuthenticationRequired = 'authentication_required';

    /**
     * The gateway's own credentials or setup are wrong -- not the customer's problem.
     */
    case Configuration = 'configuration';

    case Declined = 'declined';

    case ExpiredCard = 'expired_card';

    case Fraud = 'fraud';

    case InsufficientFunds = 'insufficient_funds';

    case Network = 'network';

    case RateLimited = 'rate_limited';

    case Unknown = 'unknown';

    /**
     * Whether retrying the same operation could plausibly succeed.
     *
     * Declines are not retriable -- retrying a declined card just declines again, and some PSPs treat it
     * as card testing. Transport faults are.
     */
    public function isRetriable(): bool
    {
        return match ($this) {
            self::Network, self::RateLimited => true,
            default => false,
        };
    }
}
