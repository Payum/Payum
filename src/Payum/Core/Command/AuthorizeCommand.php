<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\PaymentInterface;
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
final class AuthorizeCommand implements CommandInterface
{
    /**
     * @param int|null $amount in minor units. Null authorises the payment's full amount
     */
    public function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?PaymentInterface $payment = null,
        public readonly ?int $amount = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (! $this->token instanceof TokenInterface && ! $this->payment instanceof PaymentInterface) {
            throw new LogicException(sprintf(
                'An %s needs either a token or a payment: it has to know what it is authorizing.',
                self::class,
            ));
        }
    }

    public static function capability(): Capability
    {
        return Capability::Authorize;
    }

    public static function forPayment(PaymentInterface $payment, ?int $amount = null, ?string $idempotencyKey = null): self
    {
        return new self(payment: $payment, amount: $amount, idempotencyKey: $idempotencyKey);
    }

    public static function forToken(TokenInterface $token, ?int $amount = null, ?string $idempotencyKey = null): self
    {
        return new self(token: $token, amount: $amount, idempotencyKey: $idempotencyKey);
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
