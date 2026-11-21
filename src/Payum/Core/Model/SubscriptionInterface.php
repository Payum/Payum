<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use DateTimeImmutable;
use Money\Money;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionStatus;

interface SubscriptionInterface extends SubjectInterface, CustomerAwareInterface, StoredPaymentMethodAwareInterface
{
    public function getSchedule(): SubscriptionSchedule;

    /**
     * @return int|null minor units
     */
    public function getTotalAmount(): ?int;

    public function getCurrencyCode(): ?string;

    public function getMoney(): ?Money;

    public function getDescription(): ?string;

    public function getInterval(): ?BillingInterval;

    /**
     * The PSP's own plan or price, when the PSP holds the schedule.
     */
    public function getPlanReference(): ?string;

    public function getStartAt(): ?DateTimeImmutable;

    /**
     * How many charges the subscription runs for; null runs until canceled.
     */
    public function getCycles(): ?int;

    /**
     * The PSP's id for the subscription.
     */
    public function getReference(): ?string;

    public function getStatus(): SubscriptionStatus;

    public function getCurrentPeriodEnd(): ?DateTimeImmutable;

    public function getCancelAt(): ?DateTimeImmutable;

    public function getCanceledAt(): ?DateTimeImmutable;

    /**
     * Writes every non-null field of the details.
     */
    public function apply(SubscriptionDetails $details): void;
}
