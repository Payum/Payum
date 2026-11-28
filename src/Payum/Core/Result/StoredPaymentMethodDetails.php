<?php

declare(strict_types=1);

namespace Payum\Core\Result;

use Payum\Core\Model\PaymentMethodType;

/**
 * A null field means "leave as it is" when the details are recorded.
 */
final class StoredPaymentMethodDetails
{
    public function __construct(
        public readonly ?string $reference = null,
        public readonly ?PaymentMethodType $type = null,
        public readonly ?StoredPaymentMethodStatus $status = null,
        public readonly ?string $brand = null,
        public readonly ?string $last4 = null,
        public readonly ?int $expiryMonth = null,
        public readonly ?int $expiryYear = null,
        public readonly ?string $label = null,
        public readonly ?string $networkTransactionId = null,
        public readonly ?string $gatewayCustomerId = null,
    ) {
    }

    public static function active(
        string $reference,
        ?PaymentMethodType $type = null,
        ?string $brand = null,
        ?string $last4 = null,
        ?int $expiryMonth = null,
        ?int $expiryYear = null,
        ?string $label = null,
        ?string $networkTransactionId = null,
        ?string $gatewayCustomerId = null,
    ): self {
        return new self(
            $reference,
            $type,
            StoredPaymentMethodStatus::Active,
            $brand,
            $last4,
            $expiryMonth,
            $expiryYear,
            $label,
            $networkTransactionId,
            $gatewayCustomerId,
        );
    }

    public static function revoked(): self
    {
        return new self(status: StoredPaymentMethodStatus::Revoked);
    }
}
