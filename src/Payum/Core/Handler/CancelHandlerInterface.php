<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\CancelCommand;
use Payum\Core\Result\CancelResult;

/**
 * Calls the payment off before the money moves.
 */
interface CancelHandlerInterface extends HandlerInterface
{
    public function handle(CancelCommand $command, Context $context): CancelResult;
}
