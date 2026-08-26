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
use Payum\Core\Result\AuthorizeResult;
use Payum\Core\Security\TokenInterface;

/**
 * Hold the money without taking it. A later CaptureCommand settles the hold.
 *
 * Re-entrant on the same terms as {@see CaptureCommand}: a gateway that needs the customer to approve the
 * authorisation will return pending() with a NextAction and finish on the second dispatch.
 *
 * @implements CommandInterface<AuthorizeResult>
 */
final class AuthorizeCommand implements CommandInterface, HasAmount
{
    /**
     * In minor units. The lossy view of {@see self::money()}, kept because that is what
     * PaymentInterface::getTotalAmount() gives you.
     */
    public readonly ?int $amount;

    private readonly ?Money $money;

    /**
     * @param int|Money|null $amount null authorises the payment's full amount. An int is read as minor
     *                               units of the payment's currency
     */
    public function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?PaymentInterface $payment = null,
        int|Money|null $amount = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (! $this->token instanceof TokenInterface && ! $this->payment instanceof PaymentInterface) {
            throw new LogicException(sprintf(
                'An %s needs either a token or a payment: it has to know what it is authorizing.',
                self::class,
            ));
        }

        $this->money = $amount instanceof Money ? $amount : null;
        $this->amount = $amount instanceof Money ? Amount::toMinorUnits($amount) : $amount;
    }

    public static function capability(): Capability
    {
        return Capability::Authorize;
    }

    public static function forPayment(PaymentInterface $payment, int|Money|null $amount = null, ?string $idempotencyKey = null): self
    {
        return new self(payment: $payment, amount: $amount, idempotencyKey: $idempotencyKey);
    }

    public static function forToken(TokenInterface $token, int|Money|null $amount = null, ?string $idempotencyKey = null): self
    {
        return new self(token: $token, amount: $amount, idempotencyKey: $idempotencyKey);
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
