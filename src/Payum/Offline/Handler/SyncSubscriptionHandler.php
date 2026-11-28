<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\SyncSubscriptionCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\SyncSubscriptionHandlerInterface;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionResult;

final class SyncSubscriptionHandler implements SyncSubscriptionHandlerInterface
{
    public function handle(SyncSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        $subscription = $command->subscription();

        return SubscriptionResult::updated(new SubscriptionDetails(
            status: $subscription->getStatus(),
            reference: $subscription->getReference(),
            currentPeriodEnd: $subscription->getCurrentPeriodEnd(),
            cancelAt: $subscription->getCancelAt(),
            canceledAt: $subscription->getCanceledAt(),
        ));
    }
}
