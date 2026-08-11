<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * The outcome of a {@see \Payum\Core\Command\CancelCommand}.
 */
final class CancelResult extends Result
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
     * @param array<string, mixed> $raw
     */
    public static function canceled(?string $transactionId = null, array $raw = []): self
    {
        return new self(PaymentStatus::Canceled, transactionId: $transactionId, raw: $raw);
    }

    /**
     * The operation did not succeed. By default this says nothing about the payment's status: a cancel the
     * PSP refused leaves the payment exactly as it was, which is usually still authorized.
     *
     * Pass $status when the failure is terminal and the payment really has moved.
     *
     * @param array<string, mixed> $raw
     */
    public static function failed(Failure $failure, ?PaymentStatus $status = null, array $raw = []): self
    {
        return new self($status, failure: $failure, raw: $raw);
    }

    /**
     * Not finished. Some PSPs confirm a cancellation asynchronously, and a gateway needing the customer to
     * confirm passes a NextAction.
     *
     * @param array<string, mixed> $raw
     */
    public static function pending(?NextAction $next = null, array $raw = []): self
    {
        return new self(PaymentStatus::Pending, $next, raw: $raw);
    }
}
