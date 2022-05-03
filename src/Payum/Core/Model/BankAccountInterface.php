<?php

namespace Payum\Core\Model;

/**
 * Experimental. Anything could be changed in this model at any moment
 */
interface BankAccountInterface
{
    public function getHolder(): string;

    public function setHolder(string $holder);

    public function getNumber(): string;

    public function setNumber(string $number);

    public function getBankCode(): string;

    public function setBankCode(string $bankCode);

    public function getBankCountryCode(): string;

    public function setBankCountryCode(string $bankCountryCode);

    public function getIban(): string;

    public function setIban(string $iban);

    public function getBic(): string;

    public function setBic(string $bic);
}
