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
use Payum\Core\Result\CaptureResult;
use Payum\Core\Security\TokenInterface;

/**
 * Take the money.
 *
 * This command is **re-entrant** and that is by design, not an accident of the v1 implementation. On a
 * redirect gateway the same command is dispatched twice against the same URL:
 *
 *   1. First dispatch: no PSP state yet, so the handler opens the checkout and returns
 *      CaptureResult::pending() carrying a Redirect. The customer leaves.
 *   2. The PSP returns the customer to the capture token's own URL -- the very URL that dispatched the
 *      first one -- and the same command runs again. Now PSP state exists, so the handler finalises and
 *      returns CaptureResult::captured().
 *
 * The handler reads $context->state() and decides, exactly as
 * a v1 action reads the details array. Some gateways run one phase, some run three.
 *
 * @implements CommandInterface<CaptureResult>
 */
final class CaptureCommand implements CommandInterface, HasAmount
{
    /**
     * In minor units. The lossy view of {@see self::money()}, kept because that is what
     * PaymentInterface::getTotalAmount() gives you.
     */
    public readonly ?int $amount;

    private readonly ?Money $money;

    /**
     * @param TokenInterface|null $token the capture token, when this came in over HTTP. Its target URL is
     *                                   what the PSP returns the customer to, and its after URL is where
     *                                   the application goes once there is nothing left to do
     * @param PaymentInterface|null $payment set directly by a headless caller that never minted a token
     * @param int|Money|null $amount null captures the payment's full amount; a smaller value is a partial
     *                               capture, which requires Capability::PartialCapture. An int is read as
     *                               minor units of the payment's currency
     * @param string|null $idempotencyKey passed to PSPs that accept one, so a retry cannot double-charge
     */
    public function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?PaymentInterface $payment = null,
        int|Money|null $amount = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (! $this->token instanceof TokenInterface && ! $this->payment instanceof PaymentInterface) {
            throw new LogicException(sprintf(
                'A %s needs either a token or a payment: it has to know what it is capturing.',
                self::class,
            ));
        }

        $this->money = $amount instanceof Money ? $amount : null;
        $this->amount = $amount instanceof Money ? Amount::toMinorUnits($amount) : $amount;
    }

    public static function capability(): Capability
    {
        return Capability::Capture;
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
