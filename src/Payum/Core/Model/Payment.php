<?php

namespace Payum\Core\Model;

use Traversable;

class Payment implements PaymentInterface, DirectDebitPaymentInterface
{
    /**
     * @var string
     */
    protected $number;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $clientEmail;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var int
     */
    protected $totalAmount;

    /**
     * @var string
     */
    protected $currencyCode;

    /**
     * @var array
     */
    protected $details;

    /**
     * @var CreditCardInterface|null
     */
    protected $creditCard;

    /**
     * @var BankAccountInterface|null
     */
    protected $bankAccount;

    public function __construct()
    {
        $this->details = [];
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getClientEmail(): string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(string $clientEmail): void
    {
        $this->clientEmail = $clientEmail;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(int $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(string $currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @return mixed[]
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param array|Traversable $details
     */
    public function setDetails($details): void
    {
        if ($details instanceof Traversable) {
            $details = iterator_to_array($details);
        }

        $this->details = $details;
    }

    public function getCreditCard(): ?CreditCardInterface
    {
        return $this->creditCard;
    }

    public function setCreditCard(CreditCardInterface $creditCard = null): void
    {
        $this->creditCard = $creditCard;
    }

    public function getBankAccount(): ?BankAccountInterface
    {
        return $this->bankAccount;
    }

    public function setBankAccount(BankAccountInterface $bankAccount = null): void
    {
        $this->bankAccount = $bankAccount;
    }
}
