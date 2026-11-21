<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\StoredPaymentMethodStatus;

interface StoredPaymentMethodInterface extends SubjectInterface, CustomerAwareInterface
{
    /**
     * The PSP's reusable reference; null until the method has been stored.
     */
    public function getReference(): ?string;

    public function getType(): ?PaymentMethodType;

    public function getStatus(): StoredPaymentMethodStatus;

    public function getBrand(): ?string;

    public function getLast4(): ?string;

    public function getExpiryMonth(): ?int;

    public function getExpiryYear(): ?int;

    public function getLabel(): ?string;

    public function getNetworkTransactionId(): ?string;

    /**
     * Writes every non-null field of the details except the gateway customer id, which belongs to the customer.
     */
    public function apply(StoredPaymentMethodDetails $details): void;
}
