<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * The outcome of a {@see \Payum\Core\Command\NotifyCommand}.
 *
 * Carries an {@see Acknowledgement} on top of the usual outcome, because a webhook is the one operation
 * whose caller is the PSP rather than a customer.
 */
final class NotifyResult extends Result
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        ?PaymentStatus $status,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
        public readonly ?Acknowledgement $acknowledgement = null,
    ) {
        parent::__construct($status, null, $transactionId, $failure, $raw);
    }

    /**
     * The event was read and acted on.
     *
     * A null status concludes nothing about the payment, which is right for an event that reports
     * something the payment's state does not describe.
     *
     * @param array<string, mixed> $raw
     */
    public static function handled(
        ?PaymentStatus $status = null,
        ?Acknowledgement $acknowledgement = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
    ): self {
        return new self($status, transactionId: $transactionId, failure: $failure, raw: $raw, acknowledgement: $acknowledgement);
    }

    /**
     * An event type this gateway has no interest in.
     *
     * The payment is left alone and the PSP is still answered successfully. Rejecting a message because
     * it was not recognised makes the PSP redeliver it on a backoff schedule for as long as it keeps
     * failing.
     */
    public static function ignored(?Acknowledgement $acknowledgement = null): self
    {
        return new self(null, acknowledgement: $acknowledgement);
    }
}
