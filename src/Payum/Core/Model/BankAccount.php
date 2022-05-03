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
    private string $holder;

    /**
     * The account number (BBAN)
     */
    private string $number;

    /**
     * Code that identifies the bank where the account is held
     */
    private string $bankCode;

    /**
     * The bank's country code (ISO 3166-1 ALPHA-2)
     *
     * @link https://en.wikipedia.org/wiki/ISO_3166-1
     */
    private string $bankCountryCode;

    private string $iban;

    /**
     * The bank's BIC code
     *
     * @link https://en.wikipedia.org/wiki/ISO_9362
     */
    private string $bic;

    public function getHolder(): string
    {
        return $this->holder;
    }

    public function setHolder(string $holder): void
    {
        $this->holder = $holder;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    public function getBankCode(): string
    {
        return $this->bankCode;
    }

    public function setBankCode(string $bankCode): void
    {
        $this->bankCode = $bankCode;
    }

    public function getBankCountryCode(): string
    {
        return $this->bankCountryCode;
    }

    public function setBankCountryCode(string $bankCountryCode): void
    {
        $this->bankCountryCode = $bankCountryCode;
    }

    public function getIban(): string
    {
        return $this->iban;
    }

    public function setIban(string $iban): void
    {
        $this->iban = $iban;
    }

    public function getBic(): string
    {
        return $this->bic;
    }

    public function setBic(string $bic): void
    {
        $this->bic = $bic;
    }
}
