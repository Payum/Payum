<?php

declare(strict_types=1);

namespace Payum\Core\Result;

use DateTimeImmutable;

/**
 * What the PSP reports about a subscription. A null field means "leave as it is" when the details are recorded.
 */
final class SubscriptionDetails
{
    public function __construct(
        public readonly ?SubscriptionStatus $status = null,
        public readonly ?string $reference = null,
        public readonly ?DateTimeImmutable $currentPeriodEnd = null,
        public readonly ?DateTimeImmutable $cancelAt = null,
        public readonly ?DateTimeImmutable $canceledAt = null,
    ) {
    }
}
