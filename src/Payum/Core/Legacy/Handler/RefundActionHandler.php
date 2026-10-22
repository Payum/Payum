<?php

declare(strict_types=1);

namespace Payum\Core\Legacy\Handler;

use Payum\Core\Command\RefundCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\RefundHandlerInterface;
use Payum\Core\Legacy\ActionToHandlerAdapter;
use Payum\Core\Result\RefundResult;

/**
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class RefundActionHandler extends ActionToHandlerAdapter implements RefundHandlerInterface
{
    public function handle(RefundCommand $command, Context $context): RefundResult
    {
        /** @var RefundResult */
        return $this->answer($command);
    }
}
