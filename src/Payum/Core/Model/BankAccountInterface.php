<?php

namespace Payum\Core\Model;

/**
 * Experimental. Anything could be changed in this model at any moment
 */
interface BankAccountInterface
{
    /**
     * @return string
     */
    public function getHolder();

    public function setHolder(string $holder);

    /**
     * @return string
     */
    public function getNumber();

    public function setNumber(string $number);

    /**
     * @return string
     */
    public function getBankCode();

    public function setBankCode(string $bankCode);

    /**
     * @return string
     */
    public function getBankCountryCode();

    public function setBankCountryCode(string $bankCountryCode);

    /**
     * @return string
     */
    public function getIban();

    public function setIban(string $iban);

    /**
     * @return string
     */
    public function getBic();

    public function setBic(string $bic);
}
