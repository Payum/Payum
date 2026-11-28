<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\StoredPaymentMethodStatus;
use Traversable;

class StoredPaymentMethod implements StoredPaymentMethodInterface
{
    protected string $gatewayName = '';

    protected ?CustomerInterface $customer = null;

    protected ?string $reference = null;

    protected ?string $type = null;

    protected string $status = 'pending';

    protected ?string $brand = null;

    protected ?string $last4 = null;

    protected ?int $expiryMonth = null;

    protected ?int $expiryYear = null;

    protected ?string $label = null;

    protected ?string $networkTransactionId = null;

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

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): void
    {
        $this->customer = $customer;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getType(): ?PaymentMethodType
    {
        return PaymentMethodType::tryFrom($this->type ?? '');
    }

    public function getStatus(): StoredPaymentMethodStatus
    {
        return StoredPaymentMethodStatus::tryFrom($this->status) ?? StoredPaymentMethodStatus::Pending;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function getLast4(): ?string
    {
        return $this->last4;
    }

    public function getExpiryMonth(): ?int
    {
        return $this->expiryMonth;
    }

    public function getExpiryYear(): ?int
    {
        return $this->expiryYear;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getNetworkTransactionId(): ?string
    {
        return $this->networkTransactionId;
    }

    public function apply(StoredPaymentMethodDetails $details): void
    {
        $this->reference = $details->reference ?? $this->reference;
        $this->type = $details->type->value ?? $this->type;
        $this->status = $details->status->value ?? $this->status;
        $this->brand = $details->brand ?? $this->brand;
        $this->last4 = $details->last4 ?? $this->last4;
        $this->expiryMonth = $details->expiryMonth ?? $this->expiryMonth;
        $this->expiryYear = $details->expiryYear ?? $this->expiryYear;
        $this->label = $details->label ?? $this->label;
        $this->networkTransactionId = $details->networkTransactionId ?? $this->networkTransactionId;
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
