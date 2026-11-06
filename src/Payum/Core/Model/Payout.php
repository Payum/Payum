<?php

namespace Payum\Core\Model;

use Money\Money;
use Payum\Core\Money\Amount;
use Payum\Core\Result\PaymentStatus;
use Traversable;

/**
 * Experimental. Anything could be changed in this model at any moment
 */
class Payout implements PayoutInterface, MoneyAwareInterface, StatusAwareInterface
{
    /**
     * @var string
     */
    protected $recipientId;

    /**
     * @var string
     */
    protected $recipientEmail;

    /**
     * @var  int
     */
    protected $totalAmount;

    /**
     * @var string
     */
    protected $currencyCode;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $details = [];

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

    /**
     * @return string
     */
    public function getRecipientId()
    {
        return $this->recipientId;
    }

    /**
     * @param string $recipientId
     */
    public function setRecipientId($recipientId): void
    {
        $this->recipientId = $recipientId;
    }

    /**
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->recipientEmail;
    }

    /**
     * @param string $recipientEmail
     */
    public function setRecipientEmail($recipientEmail): void
    {
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * @return int
     */
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

    /**
     * @return string
     */
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

    /**
     * @return string
     */
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

    public function getStatus(): PaymentStatus
    {
        return PaymentStatus::tryFrom($this->payumStatus ?? '') ?? PaymentStatus::Unknown;
    }

    public function setStatus(PaymentStatus $status): void
    {
        $this->payumStatus = $status->value;
    }
}
