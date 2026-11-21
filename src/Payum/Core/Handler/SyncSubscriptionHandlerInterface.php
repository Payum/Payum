<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\SyncSubscriptionCommand;
use Payum\Core\Result\SubscriptionResult;

/**
 * Reads a gateway-scheduled subscription's current state from the PSP.
 */
interface SyncSubscriptionHandlerInterface extends HandlerInterface
{
    public function handle(SyncSubscriptionCommand $command, Context $context): SubscriptionResult;
}
