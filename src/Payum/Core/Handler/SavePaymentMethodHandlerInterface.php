<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\SavePaymentMethodCommand;
use Payum\Core\Result\SavePaymentMethodResult;

/**
 * Stores a payment method without taking a payment.
 *
 * Bear in mind this may be called more than once for a single method. See {@see SavePaymentMethodCommand}.
 */
interface SavePaymentMethodHandlerInterface extends HandlerInterface
{
    public function handle(SavePaymentMethodCommand $command, Context $context): SavePaymentMethodResult;
}
