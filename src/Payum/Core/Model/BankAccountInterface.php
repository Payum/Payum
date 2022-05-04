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

    /**
     * @param string $holder
     */
    public function setHolder($holder);

    /**
     * @return string
     */
    public function getNumber();

    /**
     * @param string $number
     */
    public function setNumber($number);

    /**
     * @return string
     */
    public function getBankCode();

    /**
     * @param string $bankCode
     */
    public function setBankCode($bankCode);

    /**
     * @return string
     */
    public function getBankCountryCode();

    /**
     * @param string $bankCountryCode
     */
    public function setBankCountryCode($bankCountryCode);

    /**
     * @return string
     */
    public function getIban();

    /**
     * @param string $iban
     */
    public function setIban($iban);

    /**
     * @return string
     */
    public function getBic();

    /**
     * @param string $bic
     */
    public function setBic($bic);
}
