<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Result\CancelResult;
use Payum\Core\Security\TokenInterface;

/**
 * Call the payment off before the money moves.
 *
 * Voids an authorization the merchant has decided not to settle, or abandons a payment the customer never
 * completed. Distinct from a refund, which gives back money that has already been taken.
 *
 * Merchant-initiated rather than customer-facing, so it is usually built from the payment. There is no
 * cancel token path, and a gateway needing the customer to confirm can still return a NextAction.
 *
 * @implements CommandInterface<CancelResult>
 */
final class CancelCommand implements CommandInterface
{
    /**
     * @param string|null $reason some PSPs record a reason code and surface it in their dashboard
     */
    public function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?PaymentInterface $payment = null,
        public readonly ?string $reason = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (! $this->token instanceof TokenInterface && ! $this->payment instanceof PaymentInterface) {
            throw new LogicException(sprintf(
                'A %s needs either a token or a payment: it has to know what it is canceling.',
                self::class,
            ));
        }
    }

    public static function capability(): Capability
    {
        return Capability::Cancel;
    }

    public static function forPayment(PaymentInterface $payment, ?string $reason = null): self
    {
        return new self(payment: $payment, reason: $reason);
    }

    public static function forToken(TokenInterface $token, ?string $reason = null): self
    {
        return new self(token: $token, reason: $reason);
    }

    public function payment(): ?PaymentInterface
    {
        return $this->payment;
    }

    public function token(): ?TokenInterface
    {
        return $this->token;
    }
}
