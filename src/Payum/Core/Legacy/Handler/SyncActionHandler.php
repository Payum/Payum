<?php

declare(strict_types=1);

namespace Payum\Core\Legacy\Handler;

use Payum\Core\Command\SyncCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\SyncHandlerInterface;
use Payum\Core\Legacy\ActionToHandlerAdapter;
use Payum\Core\Result\SyncResult;

/**
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class SyncActionHandler extends ActionToHandlerAdapter implements SyncHandlerInterface
{
    public function handle(SyncCommand $command, Context $context): SyncResult
    {
        /** @var SyncResult */
        return $this->answer($command);
    }
}
