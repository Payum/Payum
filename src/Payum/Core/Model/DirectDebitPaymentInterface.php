<?php
namespace Payum\Core\Model;

interface DirectDebitPaymentInterface
{
    public function getBankAccount(): ?BankAccountInterface;
}
