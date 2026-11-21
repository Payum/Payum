<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use DateTimeImmutable;
use Money\Money;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Money\Amount;
use Payum\Core\Result\SubscriptionStatus;
use Traversable;

class Subscription implements SubscriptionInterface, MoneyAwareInterface
{
    protected string $gatewayName = '';

    protected string $schedule = 'gateway';

    protected ?CustomerInterface $customer = null;

    protected ?StoredPaymentMethodInterface $storedPaymentMethod = null;

    protected ?int $totalAmount = null;

    protected ?string $currencyCode = null;

    protected ?string $description = null;

    protected ?string $interval = null;

    protected ?string $planReference = null;

    protected ?DateTimeImmutable $startAt = null;

    protected ?int $cycles = null;

    protected ?string $reference = null;

    protected string $status = 'pending';

    protected ?DateTimeImmutable $currentPeriodEnd = null;

    protected ?DateTimeImmutable $cancelAt = null;

    protected ?DateTimeImmutable $canceledAt = null;

    /**
     * @var array<mixed>
     */
    protected array $details = [];

    public function getGatewayName(): string
    {
        return $this->gatewayName;
    }

    public function setGatewayName(string $gatewayName): void
    {
        $this->gatewayName = $gatewayName;
    }

    public function getSchedule(): SubscriptionSchedule
    {
        return SubscriptionSchedule::tryFrom($this->schedule) ?? SubscriptionSchedule::Gateway;
    }

    public function setSchedule(SubscriptionSchedule $schedule): void
    {
        $this->schedule = $schedule->value;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): void
    {
        $this->customer = $customer;
    }

    public function getStoredPaymentMethod(): ?StoredPaymentMethodInterface
    {
        return $this->storedPaymentMethod;
    }

    public function setStoredPaymentMethod(?StoredPaymentMethodInterface $storedPaymentMethod): void
    {
        $this->storedPaymentMethod = $storedPaymentMethod;
    }

    public function getTotalAmount(): ?int
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?int $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(?string $currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    public function getMoney(): ?Money
    {
        return Amount::fromMinorUnits($this->totalAmount, $this->currencyCode);
    }

    public function setMoney(?Money $money): void
    {
        $this->totalAmount = $money instanceof Money ? Amount::toMinorUnits($money) : null;
        $this->currencyCode = $money?->getCurrency()->getCode();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getInterval(): ?BillingInterval
    {
        if (null === $this->interval) {
            return null;
        }

        try {
            return BillingInterval::fromString($this->interval);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    public function setInterval(?BillingInterval $interval): void
    {
        $this->interval = $interval?->toString();
    }

    public function getPlanReference(): ?string
    {
        return $this->planReference;
    }

    public function setPlanReference(?string $planReference): void
    {
        $this->planReference = $planReference;
    }

    public function getStartAt(): ?DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTimeImmutable $startAt): void
    {
        $this->startAt = $startAt;
    }

    public function getCycles(): ?int
    {
        return $this->cycles;
    }

    public function setCycles(?int $cycles): void
    {
        $this->cycles = $cycles;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): void
    {
        $this->reference = $reference;
    }

    public function getStatus(): SubscriptionStatus
    {
        return SubscriptionStatus::tryFrom($this->status) ?? SubscriptionStatus::Unknown;
    }

    public function setStatus(SubscriptionStatus $status): void
    {
        $this->status = $status->value;
    }

    public function getCurrentPeriodEnd(): ?DateTimeImmutable
    {
        return $this->currentPeriodEnd;
    }

    public function setCurrentPeriodEnd(?DateTimeImmutable $currentPeriodEnd): void
    {
        $this->currentPeriodEnd = $currentPeriodEnd;
    }

    public function getCancelAt(): ?DateTimeImmutable
    {
        return $this->cancelAt;
    }

    public function setCancelAt(?DateTimeImmutable $cancelAt): void
    {
        $this->cancelAt = $cancelAt;
    }

    public function getCanceledAt(): ?DateTimeImmutable
    {
        return $this->canceledAt;
    }

    public function setCanceledAt(?DateTimeImmutable $canceledAt): void
    {
        $this->canceledAt = $canceledAt;
    }

    public function getDetails()
    {
        return $this->details; // @phpstan-ignore return.type
    }

    /**
     * @param array<mixed>|Traversable<mixed> $details
     */
    public function setDetails($details): void
    {
        if ($details instanceof Traversable) {
            $details = iterator_to_array($details);
        }

        $this->details = $details;
    }
}
