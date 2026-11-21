<?php

declare(strict_types=1);

namespace Payum\Core\Result;

use DateTimeImmutable;
use Money\Money;

/**
 * One charge the PSP made on a subscription it schedules.
 */
final class Renewal
{
    /**
     * @param string $transactionId the PSP's id for the charge or invoice
     * @param bool $isFirst the charge that started the subscription rather than a renewal of it
     */
    public function __construct(
        public readonly string $transactionId,
        public readonly Money $amount,
        public readonly PaymentStatus $status,
        public readonly bool $isFirst = false,
        public readonly ?Failure $failure = null,
        public readonly ?DateTimeImmutable $periodEnd = null,
    ) {
    }
}
