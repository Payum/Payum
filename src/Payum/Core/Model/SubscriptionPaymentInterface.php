<?php

declare(strict_types=1);

namespace Payum\Core\Model;

interface SubscriptionPaymentInterface
{
    /**
     * A payment of a subscription is a recurring charge.
     */
    public function getSubscription(): ?SubscriptionInterface;
}
