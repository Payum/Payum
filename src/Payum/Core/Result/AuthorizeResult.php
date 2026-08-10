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
        PaymentStatus $status,
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
     * @param array<string, mixed> $raw
     */
    public static function failed(Failure $failure, array $raw = []): self
    {
        return new self(PaymentStatus::Failed, failure: $failure, raw: $raw);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function pending(?NextAction $next = null, array $raw = []): self
    {
        return new self(PaymentStatus::Pending, $next, raw: $raw);
    }
}
