<?php

declare(strict_types=1);

namespace Payum\Offline;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Result\PaymentStatus;
use function is_string;

final class OfflineDetails
{
    /**
     * @param ArrayObject<string, mixed> $details
     */
    public static function status(ArrayObject $details): PaymentStatus
    {
        $status = $details[Constants::FIELD_STATUS];

        if (! $status) {
            return PaymentStatus::New;
        }

        return (is_string($status) ? PaymentStatus::tryFrom($status) : null) ?? PaymentStatus::Unknown;
    }

    /**
     * @param ArrayObject<string, mixed> $details
     */
    public static function fillFromPayment(PaymentInterface $payment, ArrayObject $details): void
    {
        $details['amount'] = $payment->getTotalAmount();
        $details['currency'] = $payment->getCurrencyCode();
        $details['number'] = $payment->getNumber();
        $details['description'] = $payment->getDescription();
        $details['client_email'] = $payment->getClientEmail();
        $details['client_id'] = $payment->getClientId();
    }

    /**
     * @param ArrayObject<string, mixed> $details
     */
    public static function fillFromPayout(PayoutInterface $payout, ArrayObject $details): void
    {
        $details['amount'] = $payout->getTotalAmount();
        $details['currency'] = $payout->getCurrencyCode();
        $details['description'] = $payout->getDescription();
        $details['recipient_email'] = $payout->getRecipientEmail();
        $details['recipient_id'] = $payout->getRecipientId();
    }
}
