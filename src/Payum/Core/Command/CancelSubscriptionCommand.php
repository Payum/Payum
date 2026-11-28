<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Gateway\Capability;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Security\TokenInterface;

/**
 * Cancel a gateway-scheduled subscription, now or at the end of the current period.
 *
 * @implements CommandInterface<SubscriptionResult>
 */
final class CancelSubscriptionCommand implements CommandInterface
{
    /**
     * @param bool $atPeriodEnd needs a gateway that declares Capability::CancelAtPeriodEnd
     */
    public function __construct(
        private readonly SubscriptionInterface $subscription,
        public readonly bool $atPeriodEnd = false,
        public readonly ?string $idempotencyKey = null,
    ) {
    }

    public static function capability(): Capability
    {
        return Capability::CancelSubscription;
    }

    public static function forSubscription(SubscriptionInterface $subscription, bool $atPeriodEnd = false, ?string $idempotencyKey = null): self
    {
        return new self($subscription, $atPeriodEnd, $idempotencyKey);
    }

    public function subscription(): SubscriptionInterface
    {
        return $this->subscription;
    }

    public function subject(): SubjectInterface
    {
        return $this->subscription;
    }

    public function token(): ?TokenInterface
    {
        return null;
    }
}
