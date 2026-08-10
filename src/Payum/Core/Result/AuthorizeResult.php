<?php

declare(strict_types=1);

namespace Payum\Core\Result;

use DateTimeImmutable;

/**
 * The outcome of an {@see \Payum\Core\Command\AuthorizeCommand}.
 */
final class AuthorizeResult extends Result
{
    /**
     * @param array<string, mixed> $raw
     * @param int|null $authorizedAmount in minor units
     * @param DateTimeImmutable|null $expiresAt when the hold lapses, if the PSP says
     */
    public function __construct(
        ?PaymentStatus $status,
        ?NextAction $next = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
        public readonly ?int $authorizedAmount = null,
        public readonly ?DateTimeImmutable $expiresAt = null,
    ) {
        parent::__construct($status, $next, $transactionId, $failure, $raw);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function authorized(?string $transactionId = null, ?int $authorizedAmount = null, ?DateTimeImmutable $expiresAt = null, array $raw = []): self
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
