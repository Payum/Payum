<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\SyncCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\SyncHandlerInterface;
use Payum\Core\Result\SyncResult;
use Payum\Offline\OfflineDetails;

final class SyncHandler implements SyncHandlerInterface
{
    public function handle(SyncCommand $command, Context $context): SyncResult
    {
        return SyncResult::synced(OfflineDetails::status($context->state()));
    }
}
