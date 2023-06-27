<?php

namespace Payum\Core\Model;

/**
 * Experimental. Anything could be changed in this model at any moment
 */
class BankAccount implements BankAccountInterface
{
    /**
     * Name of the account holder
     */
    private ?string $holder = null;

    /**
     * The account number (BBAN)
     */
    private ?string $number = null;

    /**
     * Code that identifies the bank where the account is held
     */
    private ?string $bankCode = null;

    /**
     * The bank's country code (ISO 3166-1 ALPHA-2)
     *
     * @link https://en.wikipedia.org/wiki/ISO_3166-1
     */
    private ?string $bankCountryCode = null;

    private ?string $iban = null;

    /**
     * The bank's BIC code
     *
     * @link https://en.wikipedia.org/wiki/ISO_9362
     */
    private ?string $bic = null;

    public function getHolder(): ?string
    {
        return $this->holder;
    }

    /**
     * @param string $holder
     */
    public function setHolder($holder): void
    {
        $this->holder = $holder;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number): void
    {
        $this->number = $number;
    }

    public function getBankCode(): ?string
    {
        return $this->bankCode;
    }

    /**
     * @param string $bankCode
     */
    public function setBankCode($bankCode): void
    {
        $this->bankCode = $bankCode;
    }

    public function getBankCountryCode(): ?string
    {
        return $this->bankCountryCode;
    }

    /**
     * @param string $bankCountryCode
     */
    public function setBankCountryCode($bankCountryCode): void
    {
        $this->bankCountryCode = $bankCountryCode;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban): void
    {
        $this->iban = $iban;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic): void
    {
        $this->bic = $bic;
    }
}
