<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Result\RefundResult;
use Payum\Core\Security\TokenInterface;

/**
 * Give the money back.
 *
 * Usually a single dispatch with no customer interaction, which is what makes it the useful contrast to
 * capture: the same handler shape covers both, and only the gateway decides whether a NextAction is
 * needed.
 *
 * @implements CommandInterface<RefundResult>
 */
final class RefundCommand implements CommandInterface
{
    /**
     * @param int|null $amount in minor units. Null refunds everything captured; a smaller value is a
     *                         partial refund, which requires Capability::PartialRefund
     * @param string|null $reason some PSPs record a reason code and surface it in their dashboard
     */
    public function __construct(
        public readonly ?TokenInterface $token = null,
        public readonly ?PaymentInterface $payment = null,
        public readonly ?int $amount = null,
        public readonly ?string $reason = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (null === $this->token && null === $this->payment) {
            throw new LogicException(sprintf(
                'A %s needs either a token or a payment: it has to know what it is refunding.',
                self::class,
            ));
        }
    }

    public static function capability(): Capability
    {
        return Capability::Refund;
    }

    public static function forPayment(PaymentInterface $payment, ?int $amount = null, ?string $reason = null): self
    {
        return new self(payment: $payment, amount: $amount, reason: $reason);
    }

    public static function forToken(TokenInterface $token, ?int $amount = null, ?string $reason = null): self
    {
        return new self(token: $token, amount: $amount, reason: $reason);
    }
}
