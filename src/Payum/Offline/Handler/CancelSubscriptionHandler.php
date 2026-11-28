<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\CancelSubscriptionCommand;
use Payum\Core\Handler\CancelSubscriptionHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Result\SubscriptionStatus;
use Psr\Clock\ClockInterface;

final class CancelSubscriptionHandler implements CancelSubscriptionHandlerInterface
{
    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function handle(CancelSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        if ($command->atPeriodEnd) {
            return SubscriptionResult::updated(new SubscriptionDetails(cancelAt: $command->subscription()->getCurrentPeriodEnd()));
        }

        return SubscriptionResult::updated(new SubscriptionDetails(
            status: SubscriptionStatus::Canceled,
            canceledAt: $this->clock->now(),
        ));
    }
}
