<?php
namespace Payum\Core\Model;

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

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
    public function getClientEmail()
    {
        return $this->clientEmail;
    }

    /**
     * @param string $clientEmail
     */
    public function setClientEmail($clientEmail)
    {
        $this->clientEmail = $clientEmail;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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

    /**
     * @return CreditCardInterface|null
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * @param CreditCardInterface|null $creditCard
     */
    public function setCreditCard(CreditCardInterface $creditCard = null)
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

    /**
     * @param BankAccountInterface|null $bankAccount
     */
    public function setBankAccount(BankAccountInterface $bankAccount = null)
    {
        $this->bankAccount = $bankAccount;
    }
}
