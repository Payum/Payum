<?php

declare(strict_types=1);

namespace Payum\Core\Result;

use Money\Money;
use Payum\Core\Money\Amount;

/**
 * The outcome of a {@see \Payum\Core\Command\PayoutCommand}.
 */
final class PayoutResult extends Result
{
    /**
     * In minor units. The lossy view of {@see self::$paidOutMoney}, and null whenever that is.
     */
    public readonly ?int $paidOutAmount;

    /**
     * Set only when the handler reported a Money. Null says nothing about what went out — read
     * {@see self::$paidOutAmount} against the payout's currency.
     */
    public readonly ?Money $paidOutMoney;

    /**
     * @param array<string, mixed> $raw
     * @param int|Money|null $paidOutAmount lower than asked for when a PSP fulfils a batch in part. An int
     *                                      is read as minor units of the payout's currency
     */
    public function __construct(
        ?PaymentStatus $status,
        ?NextAction $next = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
        int|Money|null $paidOutAmount = null,
    ) {
        parent::__construct($status, $next, $transactionId, $failure, $raw);

        $this->paidOutMoney = $paidOutAmount instanceof Money ? $paidOutAmount : null;
        $this->paidOutAmount = $paidOutAmount instanceof Money ? Amount::toMinorUnits($paidOutAmount) : $paidOutAmount;
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
    public static function paidOut(?string $transactionId = null, int|Money|null $paidOutAmount = null, array $raw = []): self
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
