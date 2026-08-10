<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * Why an operation did not succeed.
 *
 * Present on a {@see Result} only when the outcome was a business failure -- a decline, a rejected card,
 * an authentication the customer abandoned. Infrastructure faults (unreachable host, malformed config,
 * a 500 from the PSP) are thrown as exceptions instead, and never reach here.
 *
 * That split is the rule to remember: **declines are results, faults are exceptions.**
 */
final class Failure
{
    public function __construct(
        public readonly FailureReason $reason,
        /**
         * The PSP's own code, verbatim, for support tickets and gateway-specific handling.
         */
        public readonly ?string $code = null,
        /**
         * The PSP's own message. Not safe to show a customer without review.
         */
        public readonly ?string $message = null,
    ) {
    }

    public function isRetriable(): bool
    {
        return $this->reason->isRetriable();
    }
}
