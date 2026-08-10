<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\RefundCommand;
use Payum\Core\Result\RefundResult;

/**
 * Gives the money back.
 */
interface RefundHandlerInterface extends HandlerInterface
{
    public function handle(RefundCommand $command, Context $context): RefundResult;
}
