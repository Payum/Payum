<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * The outcome of a {@see \Payum\Core\Command\RefundCommand}.
 *
 * A partial refund is the case v1 cannot express: its status vocabulary only has 'refunded', so a payment
 * refunded by half looked identical to one refunded in full.
 */
final class RefundResult extends Result
{
    /**
     * @param array<string, mixed> $raw
     * @param int|null $refundedAmount in minor units
     */
    public function __construct(
        PaymentStatus $status,
        ?NextAction $next = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
        public readonly ?int $refundedAmount = null,
    ) {
        parent::__construct($status, $next, $transactionId, $failure, $raw);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function failed(Failure $failure, array $raw = []): self
    {
        return new self(PaymentStatus::Failed, failure: $failure, raw: $raw);
    }

    /**
     * Some PSPs settle a refund asynchronously and confirm by webhook.
     *
     * @param array<string, mixed> $raw
     */
    public static function pending(?NextAction $next = null, array $raw = []): self
    {
        return new self(PaymentStatus::Pending, $next, raw: $raw);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function refunded(?string $transactionId = null, ?int $refundedAmount = null, array $raw = []): self
    {
        return new self(PaymentStatus::Refunded, transactionId: $transactionId, raw: $raw, refundedAmount: $refundedAmount);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function partiallyRefunded(?string $transactionId = null, ?int $refundedAmount = null, array $raw = []): self
    {
        return new self(PaymentStatus::PartiallyRefunded, transactionId: $transactionId, raw: $raw, refundedAmount: $refundedAmount);
    }
}
