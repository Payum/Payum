<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * The outcome of a {@see \Payum\Core\Command\SavePaymentMethodCommand}. It concludes nothing about a
 * payment, so its status is always null; what was stored is in {@see self::storedPaymentMethod()}.
 */
final class SavePaymentMethodResult extends Result
{
    /**
     * @param array<string, mixed> $raw
     */
    public static function saved(StoredPaymentMethodDetails $details, ?string $transactionId = null, array $raw = []): self
    {
        return (new self(null, transactionId: $transactionId, raw: $raw))->withStoredPaymentMethod($details);
    }

    /**
     * The customer has something to do, such as confirm the method with their bank.
     *
     * @param array<string, mixed> $raw
     */
    public static function pending(NextAction $next, array $raw = []): self
    {
        return new self(null, $next, raw: $raw);
    }

    /**
     * The PSP took the request on and confirms the method later, usually by webhook, as a mandate does.
     *
     * @param array<string, mixed> $raw
     */
    public static function accepted(?string $transactionId = null, array $raw = []): self
    {
        $result = new self(null, transactionId: $transactionId, raw: $raw);
        $result->accepted = true;

        return $result;
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function failed(Failure $failure, array $raw = []): self
    {
        return new self(null, failure: $failure, raw: $raw);
    }
}
