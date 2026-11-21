<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * The outcome of a {@see \Payum\Core\Command\DeletePaymentMethodCommand}. Its status is always null.
 */
final class DeletePaymentMethodResult extends Result
{
    /**
     * @param array<string, mixed> $raw
     */
    public static function deleted(array $raw = []): self
    {
        return (new self(null, raw: $raw))->withStoredPaymentMethod(StoredPaymentMethodDetails::revoked());
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function failed(Failure $failure, array $raw = []): self
    {
        return new self(null, failure: $failure, raw: $raw);
    }
}
