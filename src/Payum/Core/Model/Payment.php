<?php

namespace Payum\Core\Model;

use Money\Money;
use Payum\Core\Money\Amount;
use Payum\Core\Result\PaymentStatus;
use Traversable;

class Payment implements PaymentInterface, DirectDebitPaymentInterface, MoneyAwareInterface, StatusAwareInterface
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
    protected $details = [];

    /**
     * @var CreditCardInterface|null
     */
    protected $creditCard;

    /**
     * @var BankAccountInterface|null
     */
    protected $bankAccount;

    /**
     * Private, and not named $status, so a subclass that already declares and maps its own status keeps
     * working. Null in a row stored before the column existed, which reads as unknown.
     *
     * @phpstan-ignore property.unusedType (Doctrine hydrates null from such a row)
     */
    private ?string $payumStatus = 'new';

    public function __construct()
    {
    }

    public function getNumber()
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

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function getClientEmail()
    {
        return $this->clientEmail;
    }

    /**
     * @param string $clientEmail
     */
    public function setClientEmail($clientEmail): void
    {
        $this->clientEmail = $clientEmail;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param int $totalAmount
     */
    public function setTotalAmount($totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    public function getMoney(): ?Money
    {
        return Amount::fromMinorUnits($this->totalAmount, $this->currencyCode);
    }

    /**
     * Writes through to totalAmount and currencyCode, so the two views can never disagree and a mapping
     * that already stores them keeps working untouched.
     */
    public function setMoney(?Money $money): void
    {
        $this->totalAmount = $money instanceof Money ? Amount::toMinorUnits($money) : null;
        $this->currencyCode = $money?->getCurrency()->getCode();
    }

    public function getDetails()
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

    /**
     * @return CreditCardInterface|null
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    public function setCreditCard(?CreditCardInterface $creditCard = null): void
    {
        $this->creditCard = $creditCard;
    }

    /**
     * @return BankAccountInterface|null
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccountInterface $bankAccount = null): void
    {
        $this->bankAccount = $bankAccount;
    }

    public function getStatus(): PaymentStatus
    {
        return PaymentStatus::tryFrom($this->payumStatus ?? '') ?? PaymentStatus::Unknown;
    }

    public function setStatus(PaymentStatus $status): void
    {
        $this->payumStatus = $status->value;
    }
}
