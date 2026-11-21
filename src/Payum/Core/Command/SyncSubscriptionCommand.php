<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Gateway\Capability;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Security\TokenInterface;

/**
 * Ask the PSP for the current state of a gateway-scheduled subscription. Reads rather than mutates, so it
 * carries no idempotency key.
 *
 * @implements CommandInterface<SubscriptionResult>
 */
final class SyncSubscriptionCommand implements CommandInterface
{
    public function __construct(
        private readonly SubscriptionInterface $subscription,
    ) {
    }

    public static function capability(): Capability
    {
        return Capability::SyncSubscription;
    }

    public static function forSubscription(SubscriptionInterface $subscription): self
    {
        return new self($subscription);
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
