<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\CreateSubscriptionHandlerInterface;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Result\SubscriptionStatus;
use Psr\Clock\ClockInterface;

final class CreateSubscriptionHandler implements CreateSubscriptionHandlerInterface
{
    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function handle(CreateSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        $subscription = $context->subscription();
        $interval = $subscription?->getInterval();
        $periodStart = $subscription?->getStartAt() ?? $this->clock->now();

        return SubscriptionResult::active(new SubscriptionDetails(
            status: SubscriptionStatus::Active,
            reference: 'offline_sub_' . bin2hex(random_bytes(8)),
            currentPeriodEnd: null === $interval ? null : $periodStart->modify('+' . $interval->toString()),
        ));
    }
}
