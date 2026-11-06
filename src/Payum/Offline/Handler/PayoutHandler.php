<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\PayoutCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\PayoutHandlerInterface;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Result\PayoutResult;
use Payum\Offline\Constants;
use Payum\Offline\OfflineDetails;

final class PayoutHandler implements PayoutHandlerInterface
{
    public function handle(PayoutCommand $command, Context $context): PayoutResult
    {
        $state = $context->state();

        if ($context->payout() instanceof PayoutInterface) {
            OfflineDetails::fillFromPayout($context->payout(), $state);
        }

        $state->defaults([
            Constants::FIELD_PAYOUT => true,
        ]);

        return $state[Constants::FIELD_PAYOUT] ? PayoutResult::paidOut() : PayoutResult::pending();
    }
}
