<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\StoredPaymentMethodInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\SavePaymentMethodResult;
use Payum\Core\Security\TokenInterface;

/**
 * Store a payment method for later charges without taking a payment: a setup intent, a zero-amount
 * authorization, a mandate or a vault entry.
 *
 * Re-entrant on the same terms as a capture. On a gateway where the customer has to confirm, the first
 * dispatch returns SavePaymentMethodResult::pending() with a Redirect; the PSP returns the customer to
 * the token's own URL, the same command runs again, and the handler reads $context->state() to finalise
 * with SavePaymentMethodResult::saved().
 *
 * @implements CommandInterface<SavePaymentMethodResult>
 */
final class SavePaymentMethodCommand implements CommandInterface
{
    /**
     * @param string|null $idempotencyKey passed to PSPs that accept one, so a retry cannot store the method twice
     */
    public function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?StoredPaymentMethodInterface $method = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (! $this->token instanceof TokenInterface && ! $this->method instanceof StoredPaymentMethodInterface) {
            throw new LogicException(sprintf(
                'A %s needs either a token or a stored payment method: it has to know what it is saving into.',
                self::class,
            ));
        }
    }

    public static function capability(): Capability
    {
        return Capability::SavePaymentMethod;
    }

    public static function forMethod(StoredPaymentMethodInterface $method, ?string $idempotencyKey = null): self
    {
        return new self(method: $method, idempotencyKey: $idempotencyKey);
    }

    public static function forToken(TokenInterface $token, ?string $idempotencyKey = null): self
    {
        return new self(token: $token, idempotencyKey: $idempotencyKey);
    }

    public function method(): ?StoredPaymentMethodInterface
    {
        return $this->method;
    }

    public function subject(): ?SubjectInterface
    {
        return $this->method;
    }

    public function token(): ?TokenInterface
    {
        return $this->token;
    }
}
