<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\DeletePaymentMethodCommand;
use Payum\Core\Result\DeletePaymentMethodResult;

/**
 * Removes a stored payment method at the PSP.
 */
interface DeletePaymentMethodHandlerInterface extends HandlerInterface
{
    public function handle(DeletePaymentMethodCommand $command, Context $context): DeletePaymentMethodResult;
}
