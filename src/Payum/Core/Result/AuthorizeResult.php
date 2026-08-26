<?php

declare(strict_types=1);

namespace Payum\Core\Result;

use DateTimeImmutable;
use Money\Money;
use Payum\Core\Money\Amount;

/**
 * The outcome of an {@see \Payum\Core\Command\AuthorizeCommand}.
 */
final class AuthorizeResult extends Result
{
    /**
     * In minor units. The lossy view of {@see self::$authorizedMoney}, and null whenever that is.
     */
    public readonly ?int $authorizedAmount;

    /**
     * Set only when the handler reported a Money. Null says nothing about what was held — read
     * {@see self::$authorizedAmount} against the payment's currency.
     */
    public readonly ?Money $authorizedMoney;

    /**
     * @param array<string, mixed> $raw
     * @param int|Money|null $authorizedAmount an int is read as minor units of the payment's currency
     * @param DateTimeImmutable|null $expiresAt when the hold lapses, if the PSP says
     */
    public function __construct(
        ?PaymentStatus $status,
        ?NextAction $next = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
        int|Money|null $authorizedAmount = null,
        public readonly ?DateTimeImmutable $expiresAt = null,
    ) {
        parent::__construct($status, $next, $transactionId, $failure, $raw);

        $this->authorizedMoney = $authorizedAmount instanceof Money ? $authorizedAmount : null;
        $this->authorizedAmount = $authorizedAmount instanceof Money ? Amount::toMinorUnits($authorizedAmount) : $authorizedAmount;
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function authorized(?string $transactionId = null, int|Money|null $authorizedAmount = null, ?DateTimeImmutable $expiresAt = null, array $raw = []): self
    {
        return new self(PaymentStatus::Authorized, transactionId: $transactionId, raw: $raw, authorizedAmount: $authorizedAmount, expiresAt: $expiresAt);
    }

    /**
     * The operation did not succeed. By default this says nothing about the payment's status: a declined
     * authorization leaves it where it was, which is what lets a customer try again.
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
     * @param array<string, mixed> $raw
     */
    public static function pending(?NextAction $next = null, array $raw = []): self
    {
        return new self(PaymentStatus::Pending, $next, raw: $raw);
    }
}
