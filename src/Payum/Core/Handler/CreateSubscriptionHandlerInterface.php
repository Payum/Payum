<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Result\SubscriptionResult;

/**
 * Creates a subscription the PSP schedules.
 *
 * Bear in mind this may be called more than once for a single subscription. See {@see CreateSubscriptionCommand}.
 */
interface CreateSubscriptionHandlerInterface extends HandlerInterface
{
    public function handle(CreateSubscriptionCommand $command, Context $context): SubscriptionResult;
}
