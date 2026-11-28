<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Gateway\Capability;
use Payum\Core\Model\StoredPaymentMethodInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\DeletePaymentMethodResult;
use Payum\Core\Security\TokenInterface;

/**
 * Remove a stored payment method at the PSP, so it can no longer be charged.
 *
 * @implements CommandInterface<DeletePaymentMethodResult>
 */
final class DeletePaymentMethodCommand implements CommandInterface
{
    public function __construct(
        private readonly StoredPaymentMethodInterface $method,
        public readonly ?string $idempotencyKey = null,
    ) {
    }

    public static function capability(): Capability
    {
        return Capability::DeletePaymentMethod;
    }

    public static function forMethod(StoredPaymentMethodInterface $method, ?string $idempotencyKey = null): self
    {
        return new self($method, $idempotencyKey);
    }

    public function method(): StoredPaymentMethodInterface
    {
        return $this->method;
    }

    public function subject(): SubjectInterface
    {
        return $this->method;
    }

    public function token(): ?TokenInterface
    {
        return null;
    }
}
