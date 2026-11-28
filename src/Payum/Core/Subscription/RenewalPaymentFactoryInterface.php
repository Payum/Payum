<?php

declare(strict_types=1);

namespace Payum\Core\Subscription;

use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Result\Renewal;

/**
 * Makes the payment that records a charge the PSP took on a subscription it schedules.
 */
interface RenewalPaymentFactoryInterface
{
    /**
     * @return PaymentInterface an unsaved payment; core sets its status and saves it
     */
    public function create(SubscriptionInterface $subscription, Renewal $renewal): PaymentInterface;
}
