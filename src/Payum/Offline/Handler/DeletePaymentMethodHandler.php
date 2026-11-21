<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\DeletePaymentMethodCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\DeletePaymentMethodHandlerInterface;
use Payum\Core\Result\DeletePaymentMethodResult;

final class DeletePaymentMethodHandler implements DeletePaymentMethodHandlerInterface
{
    public function handle(DeletePaymentMethodCommand $command, Context $context): DeletePaymentMethodResult
    {
        return DeletePaymentMethodResult::deleted();
    }
}
