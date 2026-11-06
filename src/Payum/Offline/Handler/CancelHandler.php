<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\CancelCommand;
use Payum\Core\Handler\CancelHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CancelResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\PaymentStatus;
use Payum\Offline\OfflineDetails;
use function in_array;
use function sprintf;

final class CancelHandler implements CancelHandlerInterface
{
    public function handle(CancelCommand $command, Context $context): CancelResult
    {
        $status = OfflineDetails::status($context->state());

        if (in_array($status, [PaymentStatus::New, PaymentStatus::Pending, PaymentStatus::Authorized, PaymentStatus::Canceled], true)) {
            return CancelResult::canceled();
        }

        return CancelResult::failed(new Failure(
            FailureReason::Declined,
            'not_cancelable',
            sprintf('Only a new, pending or authorized payment can be canceled, and this one is %s.', $status->value),
        ));
    }
}
