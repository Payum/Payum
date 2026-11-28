<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\CancelSubscriptionCommand;
use Payum\Core\Result\SubscriptionResult;

/**
 * Cancels a gateway-scheduled subscription.
 */
interface CancelSubscriptionHandlerInterface extends HandlerInterface
{
    public function handle(CancelSubscriptionCommand $command, Context $context): SubscriptionResult;
}
