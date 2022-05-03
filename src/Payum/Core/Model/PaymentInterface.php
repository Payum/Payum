<?php
namespace Payum\Core\Model;

/**
 * @method array getDetails()
 */
interface PaymentInterface extends CreditCardPaymentInterface, DetailsAggregateInterface, DetailsAwareInterface
{
    public function getNumber(): string;

    public function getDescription(): string;

    public function getClientEmail(): string;

    public function getClientId(): string;

    public function getTotalAmount(): int;

    public function getCurrencyCode(): string;
}
