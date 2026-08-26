<?php

declare(strict_types=1);

namespace Payum\Core\Result;

use Money\Money;
use Payum\Core\Money\Amount;

/**
 * The outcome of a {@see \Payum\Core\Command\RefundCommand}.
 *
 * A partial refund is the case v1 cannot express: its status vocabulary only has 'refunded', so a payment
 * refunded by half looked identical to one refunded in full.
 */
final class RefundResult extends Result
{
    /**
     * In minor units. The lossy view of {@see self::$refundedMoney}, and null whenever that is.
     */
    public readonly ?int $refundedAmount;

    /**
     * Set only when the handler reported a Money. Null says nothing about what went back — read
     * {@see self::$refundedAmount} against the payment's currency.
     */
    public readonly ?Money $refundedMoney;

    /**
     * @param array<string, mixed> $raw
     * @param int|Money|null $refundedAmount an int is read as minor units of the payment's currency
     */
    public function __construct(
        ?PaymentStatus $status,
        ?NextAction $next = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
        int|Money|null $refundedAmount = null,
    ) {
        parent::__construct($status, $next, $transactionId, $failure, $raw);

        $this->refundedMoney = $refundedAmount instanceof Money ? $refundedAmount : null;
        $this->refundedAmount = $refundedAmount instanceof Money ? Amount::toMinorUnits($refundedAmount) : $refundedAmount;
    }

    /**
     * The operation did not succeed. By default this says nothing about the payment's status: a declined
     * refund leaves it where it was, which is what lets a customer try again.
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
    public static function refunded(?string $transactionId = null, int|Money|null $refundedAmount = null, array $raw = []): self
    {
        return new self(PaymentStatus::Refunded, transactionId: $transactionId, raw: $raw, refundedAmount: $refundedAmount);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function partiallyRefunded(?string $transactionId = null, int|Money|null $refundedAmount = null, array $raw = []): self
    {
        return new self(PaymentStatus::PartiallyRefunded, transactionId: $transactionId, raw: $raw, refundedAmount: $refundedAmount);
    }
}
