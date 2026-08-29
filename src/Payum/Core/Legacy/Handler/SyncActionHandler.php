<?php

declare(strict_types=1);

namespace Payum\Core\Legacy\Handler;

use Payum\Core\Command\SyncCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\SyncHandlerInterface;
use Payum\Core\Legacy\ActionToHandlerAdapter;
use Payum\Core\Request\Sync;
use Payum\Core\Result\SyncResult;

/**
 * A 1.x sync action, answering a {@see SyncCommand}.
 *
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class SyncActionHandler extends ActionToHandlerAdapter implements SyncHandlerInterface
{
    public function handle(SyncCommand $command, Context $context): SyncResult
    {
        [$status, $next] = $this->run(Sync::class, $context);

        return new SyncResult($status, $next);
    }
}
