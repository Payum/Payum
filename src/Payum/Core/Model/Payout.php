<?php
namespace Payum\Core\Model;

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
    public function setRecipientId($recipientId)
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
    public function setRecipientEmail($recipientEmail)
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
    public function setTotalAmount($totalAmount)
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
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
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
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritDoc}
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * {@inheritDoc}
     *
     * @param array|\Traversable $details
     */
    public function setDetails($details)
    {
        if ($details instanceof \Traversable) {
            $details = iterator_to_array($details);
        }

        $this->details = $details;
    }
}
