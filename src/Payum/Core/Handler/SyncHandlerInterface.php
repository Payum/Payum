<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\SyncCommand;
use Payum\Core\Result\SyncResult;

/**
 * Re-reads the current state from the PSP.
 */
interface SyncHandlerInterface extends HandlerInterface
{
    public function handle(SyncCommand $command, Context $context): SyncResult;
}
