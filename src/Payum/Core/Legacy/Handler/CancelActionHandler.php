<?php

declare(strict_types=1);

namespace Payum\Core\Legacy\Handler;

use Payum\Core\Command\CancelCommand;
use Payum\Core\Handler\CancelHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\ActionToHandlerAdapter;
use Payum\Core\Result\CancelResult;

/**
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class CancelActionHandler extends ActionToHandlerAdapter implements CancelHandlerInterface
{
    public function handle(CancelCommand $command, Context $context): CancelResult
    {
        /** @var CancelResult */
        return $this->answer($command);
    }
}
