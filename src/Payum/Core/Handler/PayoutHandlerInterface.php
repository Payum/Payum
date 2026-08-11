<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\PayoutCommand;
use Payum\Core\Result\PayoutResult;

/**
 * Sends money out to a recipient.
 */
interface PayoutHandlerInterface extends HandlerInterface
{
    public function handle(PayoutCommand $command, Context $context): PayoutResult;
}
