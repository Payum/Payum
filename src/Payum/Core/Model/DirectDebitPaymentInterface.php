<?php
namespace Payum\Core\Model;

interface DirectDebitPaymentInterface extends PaymentInterface
{
    /**
     * @return BankAccountInterface|null
     */
    public function getBankAccount();
}
