<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * The outcome of a {@see \Payum\Core\Command\SyncCommand}.
 *
 * The only result whose status is an argument rather than fixed by the constructor that built it: a sync
 * reports whatever the PSP says, which could be any state at all. Recording it is what makes a sync worth
 * running -- the stored status catches up with the PSP.
 */
final class SyncResult extends Result
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        ?PaymentStatus $status,
        ?NextAction $next = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
    ) {
        parent::__construct($status, $next, $transactionId, $failure, $raw);
    }

    /**
     * The PSP could not be asked, or answered with something unusable. The stored status is left alone,
     * since a failed read is not evidence that anything changed.
     *
     * @param array<string, mixed> $raw
     */
    public static function failed(Failure $failure, ?PaymentStatus $status = null, array $raw = []): self
    {
        return new self($status, failure: $failure, raw: $raw);
    }

    /**
     * @param array<string, mixed> $raw the PSP's answer, which is usually the point of asking
     */
    public static function synced(PaymentStatus $status, ?string $transactionId = null, array $raw = []): self
    {
        return new self($status, transactionId: $transactionId, raw: $raw);
    }
}
