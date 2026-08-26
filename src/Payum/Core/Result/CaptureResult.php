<?php

declare(strict_types=1);

namespace Payum\Core\Result;

use Money\Money;
use Payum\Core\Money\Amount;

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
     * In minor units. The lossy view of {@see self::$capturedMoney}, and null whenever that is.
     */
    public readonly ?int $capturedAmount;

    /**
     * Set only when the handler reported a Money. Null says nothing about what was taken — read
     * {@see self::$capturedAmount} against the payment's currency.
     */
    public readonly ?Money $capturedMoney;

    /**
     * @param array<string, mixed> $raw
     * @param int|Money|null $capturedAmount null means the payment's full amount. An int is read as minor
     *                                       units of the payment's currency
     */
    public function __construct(
        ?PaymentStatus $status,
        ?NextAction $next = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
        int|Money|null $capturedAmount = null,
    ) {
        parent::__construct($status, $next, $transactionId, $failure, $raw);

        $this->capturedMoney = $capturedAmount instanceof Money ? $capturedAmount : null;
        $this->capturedAmount = $capturedAmount instanceof Money ? Amount::toMinorUnits($capturedAmount) : $capturedAmount;
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
    public static function captured(?string $transactionId = null, int|Money|null $capturedAmount = null, array $raw = []): self
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
