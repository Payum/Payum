<?php
namespace Payum\Core\Model;

interface DirectDebitPaymentInterface
{
    /**
     * @return BankAccountInterface|null
     */
    public function getBankAccount();
}
