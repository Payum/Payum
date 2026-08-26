<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Money\Currency;
use Money\Money;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Money\Amount;
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
final class RefundCommand implements CommandInterface, HasAmount
{
    /**
     * In minor units. The lossy view of {@see self::money()}, kept because that is what
     * PaymentInterface::getTotalAmount() gives you.
     */
    public readonly ?int $amount;

    private readonly ?Money $money;

    /**
     * @param int|Money|null $amount null refunds everything captured; a smaller value is a partial refund,
     *                               which requires Capability::PartialRefund. An int is read as minor
     *                               units of the payment's currency
     * @param string|null $reason some PSPs record a reason code and surface it in their dashboard
     */
    public function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?PaymentInterface $payment = null,
        int|Money|null $amount = null,
        public readonly ?string $reason = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (! $this->token instanceof TokenInterface && ! $this->payment instanceof PaymentInterface) {
            throw new LogicException(sprintf(
                'A %s needs either a token or a payment: it has to know what it is refunding.',
                self::class,
            ));
        }

        $this->money = $amount instanceof Money ? $amount : null;
        $this->amount = $amount instanceof Money ? Amount::toMinorUnits($amount) : $amount;
    }

    public static function capability(): Capability
    {
        return Capability::Refund;
    }

    public static function forPayment(PaymentInterface $payment, int|Money|null $amount = null, ?string $reason = null): self
    {
        return new self(payment: $payment, amount: $amount, reason: $reason);
    }

    public static function forToken(TokenInterface $token, int|Money|null $amount = null, ?string $reason = null): self
    {
        return new self(token: $token, amount: $amount, reason: $reason);
    }

    public function money(?Currency $currency = null): ?Money
    {
        return $this->money ?? Amount::fromMinorUnits($this->amount, $currency?->getCode() ?? $this->payment?->getCurrencyCode());
    }

    public function payment(): ?PaymentInterface
    {
        return $this->payment;
    }

    public function subject(): ?SubjectInterface
    {
        return $this->payment;
    }

    public function token(): ?TokenInterface
    {
        return $this->token;
    }
}
