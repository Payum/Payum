<?php
namespace Payum\Core\Model;

class Payment implements PaymentInterface
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
    protected $workPhone;

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

    protected $creditCardNumber;

    protected $creditCardExpireAt;

    protected $userName;

    protected $expireMonth;

    protected $expireYear;

    protected $cardName;

    protected $firstName;

    protected $lastName;

    protected $cardType;

    protected $addressZip;

    protected $addressState;

    protected $addressCountry;

    protected $addressLine1;

    protected $addressLine2;

    protected $eventStatus;

    protected $company;

    protected $attendeeID;

    protected $eventID;

    protected $eventName;

    protected $env;

    protected $orderNumber;

    protected $TransactionID;

    protected $refund;

    /**
     * @return mixed
     */
    public function getIsraelisocialid()
    {
        return $this->israelisocialid;
    }

    /**
     * @param mixed $israelisocialid
     */
    public function setIsraelisocialid($israelisocialid)
    {
        $this->israelisocialid = $israelisocialid;
    }

    protected $israelisocialid;

    public function __construct()
    {
        $this->details = array();
    }

    public function getCreditCardNumber()
    {
        return $this->creditCardNumber;
    }

    public function setCreditCardNumber($creditCardNumber)
    {
        $this->creditCardNumber = $creditCardNumber;
    }

    public function getCreditCardExpireAt()
    {
        return $this->creditCardExpireAt;
    }

    public function setCreditCardExpireAt($creditCardExpireAt)
    {
        $this->creditCardExpireAt = $creditCardExpireAt;
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
    public function getWorkPhone()
    {
        return $this->workPhone;
    }

    /**
     * @param string $clientEmail
     */
    public function setWorkPhone($workPhone)
    {
        $this->workPhone = $workPhone;
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

    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param int $totalAmount
     */
    public function setUserName($userName)
    {
        $this->userName = $UserName;
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

    public function setExpireMonth($month)
    {
        $this->expireMonth = $month;
    }

    public function getExpireMonth()
    {
        return $this->expireMonth;
    }

    public function setExpireYear($year)
    {
        $this->expireYear = $year;
    }

    public function getExpireYear()
    {
        return $this->expireYear;
    }

    public function setCardName($name)
    {
        $this->cardName = $name;
    }

    public function getCardName()
    {
        return $this->cardName;
    }

    public function setFirstName($fname)
    {
        $this->firstName = $fname;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setLastName($lname)
    {
        $this->lastName = $lname;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setCardType($cardtype)
    {
        $this->cardType = $cardtype;
    }

    public function getCardType()
    {
        return $this->cardType;
    }

    public function setAddressZip($zip)
    {
        $this->addressZip = $zip;
    }

    public function getAddressZip()
    {
        return $this->addressZip;
    }

    public function setAddressState($state)
    {
        $this->addressState = $state;
    }

    public function getAddressState()
    {
        return $this->addressState;
    }

    public function setAddressCountry($country)
    {
        $this->addressCountry = $country;
    }

    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    public function setAddressLine1($address)
    {
        $this->addressLine1 = $address;
    }

    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    public function setAddressLine2($address)
    {
        $this->addressLine2 = $address;
    }

    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    public function setEventStatus($status)
    {
        $this->eventStatus = $status;
    }

    public function getEventStatus()
    {
        return $this->eventStatus;
    }

    public function setCompany($company)
    {
        $this->company = $company;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setAttendeeID($attendee)
    {
        $this->attendeeID = $attendee;
    }

    public function getAttendeeID()
    {
        return $this->attendeeID;
    }

    public function setEventID($event)
    {
        $this->eventID = $event;
    }

    public function getEventID()
    {
        return $this->eventID;
    }

    public function setEventName($event)
    {
        $this->eventName = $event;
    }

    public function getEventName()
    {
        return $this->eventName;
    }

    public function setEnvironment($env)
    {
        $this->env = $env;
    }

    public function getEnvironment()
    {
        return $this->env;
    }

    public function setOrderNumber($order)
    {
        $this->orderNumber = $order;
    }

    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    public function setTransactionID($ID)
    {
        $this->TransactionID = $ID;
    }

    public function getTransactionID()
    {
        return $this->TransactionID;
    }

    public function setRefund($refund)
    {
        $this->refund = $refund;
    }

    public function getRefund()
    {
        return $this->refund;
    }

}
