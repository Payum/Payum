<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Mocks\Model;

use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Model\PaymentInterface;

/**
 * A payment that does not implement StatusAwareInterface, as a 1.x application's own model may not.
 */
class UntrackedPayment implements PaymentInterface
{
    private object $details;

    public function __construct()
    {
        $this->details = new ArrayObject();
    }

    public function getNumber(): string
    {
        return 'aNumber';
    }

    public function getDescription(): string
    {
        return 'aDescription';
    }

    public function getClientEmail(): string
    {
        return 'client@example.com';
    }

    public function getClientId(): string
    {
        return 'aClientId';
    }

    public function getTotalAmount(): int
    {
        return 123;
    }

    public function getCurrencyCode(): string
    {
        return 'EUR';
    }

    public function getCreditCard(): ?CreditCardInterface
    {
        return null;
    }

    public function getDetails(): object
    {
        return $this->details;
    }

    /**
     * @param object $details
     */
    public function setDetails($details): void
    {
        $this->details = $details;
    }
}
