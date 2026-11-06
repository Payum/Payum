<?php

declare(strict_types=1);

namespace Payum\Offline\Handler;

use Payum\Core\Command\RefundCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\RefundHandlerInterface;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\RefundResult;
use Payum\Offline\Constants;
use Payum\Offline\OfflineDetails;
use function in_array;
use function sprintf;

final class RefundHandler implements RefundHandlerInterface
{
    public function handle(RefundCommand $command, Context $context): RefundResult
    {
        $state = $context->state();
        $status = OfflineDetails::status($state);

        if (! in_array($status, [PaymentStatus::Captured, PaymentStatus::PartiallyRefunded], true)) {
            return RefundResult::failed(new Failure(
                FailureReason::Declined,
                'not_captured',
                sprintf('Only a captured payment can be refunded, and this one is %s.', $status->value),
            ));
        }

        $total = (int) $context->payment()?->getTotalAmount();
        $refunded = (int) $state[Constants::FIELD_REFUNDED_AMOUNT];
        $amount = $command->amount ?? $total - $refunded;

        if ($amount <= 0 || $refunded + $amount > $total) {
            return RefundResult::failed(new Failure(
                FailureReason::Declined,
                'amount_exceeds_balance',
                sprintf('Cannot refund %d of a payment with %d left to refund.', $amount, $total - $refunded),
            ));
        }

        $state[Constants::FIELD_REFUNDED_AMOUNT] = $refunded + $amount;

        return $refunded + $amount < $total
            ? RefundResult::partiallyRefunded(refundedAmount: $amount)
            : RefundResult::refunded(refundedAmount: $amount);
    }
}
