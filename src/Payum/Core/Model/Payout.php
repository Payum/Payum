<?php

namespace Payum\Core\Model;

use Traversable;

/**
 * Experimental. Anything could be changed in this model at any moment
 */
class Payout implements PayoutInterface
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
    protected $details;

    public function __construct()
    {
        $this->details = [];
    }

    public function getRecipientId(): string
    {
        return $this->recipientId;
    }

    public function setRecipientId(string $recipientId): void
    {
        $this->recipientId = $recipientId;
    }

    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }

    public function setRecipientEmail(string $recipientEmail): void
    {
        $this->recipientEmail = $recipientEmail;
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
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
}
