<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\SavePaymentMethodCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\SavePaymentMethodHandlerInterface;
use Payum\Core\Result\SavePaymentMethodResult;
use Payum\Offline\OfflineDetails;

final class SavePaymentMethodHandler implements SavePaymentMethodHandlerInterface
{
    public function handle(SavePaymentMethodCommand $command, Context $context): SavePaymentMethodResult
    {
        return SavePaymentMethodResult::saved(OfflineDetails::storedPaymentMethod());
    }
}
