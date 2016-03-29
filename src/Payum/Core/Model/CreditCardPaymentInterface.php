<?php
namespace Payum\Core\Model;

/**
 * @experimental
 */
interface CreditCardPaymentInterface
{
    /**
     * @return CreditCardInterface|null
     */
    public function getCreditCard();
}
