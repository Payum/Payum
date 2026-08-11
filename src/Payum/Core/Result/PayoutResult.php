<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * The outcome of a {@see \Payum\Core\Command\PayoutCommand}.
 */
final class PayoutResult extends Result
{
    /**
     * @param array<string, mixed> $raw
     * @param int|null $paidOutAmount in minor units. Lower than asked for when a PSP fulfils a batch in
     *                                part
     */
    public function __construct(
        ?PaymentStatus $status,
        ?NextAction $next = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
        public readonly ?int $paidOutAmount = null,
    ) {
        parent::__construct($status, $next, $transactionId, $failure, $raw);
    }

    /**
     * The operation did not succeed. By default this says nothing about the payout's status: a rejected
     * payout leaves it where it was, ready to be tried again.
     *
     * Pass $status when the failure is terminal.
     *
     * @param array<string, mixed> $raw
     */
    public static function failed(Failure $failure, ?PaymentStatus $status = null, array $raw = []): self
    {
        return new self($status, failure: $failure, raw: $raw);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function paidOut(?string $transactionId = null, ?int $paidOutAmount = null, array $raw = []): self
    {
        return new self(PaymentStatus::PaidOut, transactionId: $transactionId, raw: $raw, paidOutAmount: $paidOutAmount);
    }

    /**
     * Payouts are commonly submitted and settled later, so this is the usual first answer.
     *
     * @param array<string, mixed> $raw
     */
    public static function pending(?NextAction $next = null, array $raw = []): self
    {
        return new self(PaymentStatus::Pending, $next, raw: $raw);
    }
}
