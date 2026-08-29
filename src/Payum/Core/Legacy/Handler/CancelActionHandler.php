<?php

declare(strict_types=1);

namespace Payum\Core\Legacy\Handler;

use Payum\Core\Command\CancelCommand;
use Payum\Core\Handler\CancelHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\ActionToHandlerAdapter;
use Payum\Core\Request\Cancel;
use Payum\Core\Result\CancelResult;

/**
 * A 1.x cancel action, answering a {@see CancelCommand}.
 *
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class CancelActionHandler extends ActionToHandlerAdapter implements CancelHandlerInterface
{
    public function handle(CancelCommand $command, Context $context): CancelResult
    {
        [$status, $next] = $this->run(Cancel::class, $context);

        return new CancelResult($status, $next);
    }
}
