<?php

namespace Payum\Core\Model;

/**
 * @method array getDetails()
 */
interface PaymentInterface extends CreditCardPaymentInterface, SubjectInterface
{
    /**
     * @return string
     */
    public function getNumber();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getClientEmail();

    /**
     * @return string
     */
    public function getClientId();

    /**
     * @return int|null minor units
     */
    public function getTotalAmount();

    /**
     * @return string|null ISO 4217 alpha-3, or a code registered on the gateway's Money\Currencies
     */
    public function getCurrencyCode();
}
