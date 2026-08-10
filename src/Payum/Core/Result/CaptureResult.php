<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * The outcome of a {@see \Payum\Core\Command\CaptureCommand}.
 *
 * Remember that capture is re-entrant: the first call on a redirect gateway returns pending() with a
 * Redirect, and the second call -- after the customer returns to the same capture URL -- returns
 * captured(). Both are this type.
 */
final class CaptureResult extends Result
{
    /**
     * @param array<string, mixed> $raw
     * @param int|null $capturedAmount in minor units; null means the payment's full amount
     */
    public function __construct(
        ?PaymentStatus $status,
        ?NextAction $next = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
        public readonly ?int $capturedAmount = null,
    ) {
        parent::__construct($status, $next, $transactionId, $failure, $raw);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function canceled(array $raw = []): self
    {
        return new self(PaymentStatus::Canceled, raw: $raw);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function captured(?string $transactionId = null, ?int $capturedAmount = null, array $raw = []): self
    {
        return new self(PaymentStatus::Captured, transactionId: $transactionId, raw: $raw, capturedAmount: $capturedAmount);
    }

    /**
     * The operation did not succeed. By default this says nothing about the payment's status: a declined
     * capture leaves it where it was, which is what lets a customer try again.
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
     * Not finished. Pass the NextAction when the customer has something to do, and omit it when the
     * gateway is simply waiting on the PSP.
     *
     * @param array<string, mixed> $raw
     */
    public static function pending(?NextAction $next = null, array $raw = []): self
    {
        return new self(PaymentStatus::Pending, $next, raw: $raw);
    }
}
