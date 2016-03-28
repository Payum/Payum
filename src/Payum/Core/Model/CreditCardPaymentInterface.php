<?php
namespace Payum\Core\Model;

/**
 * @experimental
 */
interface CreditCardPaymentInterface extends PaymentInterface
{
    /**
     * @return CreditCardInterface|null
     */
    public function getCreditCard();
}
