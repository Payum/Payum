<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Security\TokenInterface;

/**
 * Create a subscription that the PSP schedules and charges.
 *
 * Re-entrant on the same terms as a capture. On a gateway where the customer has to approve, the first
 * dispatch returns SubscriptionResult::pending() with a Redirect; the PSP returns the customer to the
 * token's own URL, the same command runs again, and the handler reads $context->state() to finalise with
 * SubscriptionResult::active().
 *
 * @implements CommandInterface<SubscriptionResult>
 */
final class CreateSubscriptionCommand implements CommandInterface
{
    /**
     * @param string|null $idempotencyKey passed to PSPs that accept one, so a retry cannot create the subscription twice
     */
    public function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?SubscriptionInterface $subscription = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (! $this->token instanceof TokenInterface && ! $this->subscription instanceof SubscriptionInterface) {
            throw new LogicException(sprintf(
                'A %s needs either a token or a subscription: it has to know what it is creating.',
                self::class,
            ));
        }
    }

    public static function capability(): Capability
    {
        return Capability::CreateSubscription;
    }

    public static function forSubscription(SubscriptionInterface $subscription, ?string $idempotencyKey = null): self
    {
        return new self(subscription: $subscription, idempotencyKey: $idempotencyKey);
    }

    public static function forToken(TokenInterface $token, ?string $idempotencyKey = null): self
    {
        return new self(token: $token, idempotencyKey: $idempotencyKey);
    }

    public function subscription(): ?SubscriptionInterface
    {
        return $this->subscription;
    }

    public function subject(): ?SubjectInterface
    {
        return $this->subscription;
    }

    public function token(): ?TokenInterface
    {
        return $this->token;
    }
}
