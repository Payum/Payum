<?php

namespace Payum\Core\Model;

/**
 * Experimental. Anything could be changed in this model at any moment
 */
class BankAccount implements BankAccountInterface
{
    /**
     * Name of the account holder
     *
     * @var string
     */
    private $holder;

    /**
     * The account number (BBAN)
     *
     * @var string
     */
    private $number;

    /**
     * Code that identifies the bank where the account is held
     *
     * @var string
     */
    private $bankCode;

    /**
     * The bank's country code (ISO 3166-1 ALPHA-2)
     *
     * @link https://en.wikipedia.org/wiki/ISO_3166-1
     * @var string
     */
    private $bankCountryCode;

    /**
     * @var string
     */
    private $iban;

    /**
     * The bank's BIC code
     *
     * @link https://en.wikipedia.org/wiki/ISO_9362
     * @var string
     */
    private $bic;

    /**
     * @return string
     */
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * @param string $holder
     */
    public function setHolder($holder)
    {
        $this->holder = $holder;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * @param string $bankCode
     */
    public function setBankCode($bankCode)
    {
        $this->bankCode = $bankCode;
    }

    /**
     * @return string
     */
    public function getBankCountryCode()
    {
        return $this->bankCountryCode;
    }

    /**
     * @param string $bankCountryCode
     */
    public function setBankCountryCode($bankCountryCode)
    {
        $this->bankCountryCode = $bankCountryCode;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }
}
