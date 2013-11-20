<?php
namespace Payum\Be2Bill\Model;

use Payum\Exception\InvalidArgumentException;

/**
 * @deprecated since 0.6.2 will be removed in 0.7
 */
class PaymentDetails implements \ArrayAccess, \IteratorAggregate
{
    /**
     * Description: Action to be carried out
     * Type: authorization, payment, capture, refund, credit
     * Example: payment
     * 
     * @var string
     */
    protected $operationtype;

    /**
     * Description: Transaction cart description 
     * Type: String (510 max)
     * Example: Computer equipment purchasing
     * 
     * @var string 
     */
    protected $description;

    /**
     * Description: Unique order (or cart) number on the merchant site 
     * Type: String (40 max)
     * Example: order_1234_456
     *
     * @var string
     */
    protected $orderid;

    /**
     * Description: Sum total of the transaction in cents
     * Type: Integer
     * Example: 1000 (for 10 Euros)
     *
     * @var int
     */
    protected $amount;

    /**
     * Description: Cardholder's card type
     * Type: Visa, Mastercard
     * Example: Visa
     *
     * @var string
     */
    protected $cardtype;

    /**
     * Description: Cardholder's unique identifier on the merchant site
     * Type: String (255 max)
     * Example: John.doe42 or user.id=42
     *
     * @var string
     */
    protected $clientident;

    /**
     * Description: Cardholder's email
     * Type: E-Mail (255 max)
     * Example: John.doe@isp.com
     *
     * @var string
     */
    protected $clientemail;

    /**
     * Description: Cardholder's address
     * Type: String (510 max)
     * Example: 42 avenue des Champs Elysées 75008 Paris
     *
     * @var string
     */
    protected $clientaddress;

    /**
     * Description: Cardholder's date of birth
     * Type: YYYY-MM-DD
     * Example: 1982-01-05
     *
     * @var string
     */
    protected $clientdob;

    /**
     * Description: Cardholder's source URL
     * Type: String (255 max)
     * Example: http://site.com/cart.php
     *
     * @var string
     */
    protected $clientreferer;

    /**
     * Description: Cardholder's browser
     * Type: String (255 max)
     * Example: Firefox
     *
     * @var string
     */
    protected $clientuseragent;

    /**
     * Description: Cardholder's IP address
     * Type: String (15 max)
     * Example: 10.10.10.10
     *
     * @var string
     */
    protected $clientip;

    /**
     * Description: Cardholder's first name
     * Type: String (127 max)
     * Example: John
     *
     * @var string
     */
    protected $firstname;

    /**
     * Description: Cardholder's last name
     * Type: String (127 max)
     * Example: Doe
     *
     * @var string
     */
    protected $lastname;

    /**
     * Description: Cardholder's language
     * Type: fr or en
     * Example: fr
     *
     * @var string
     */
    protected $language;

    /**
     * Description: Request the creation of an ALIAS for subsequent use in ONECLICK or SUBSCRIBTION mode
     * Type: yes or no
     * Example: yes
     *
     * @var string
     */
    protected $createalias;

    /**
     * Description: The identifier for referencing the cardholder's card
     * Type: String (32 max)
     * Example: AB132465465
     *
     * @var string
     */
    protected $alias;

    /**
     * Description: Recurrence mode applied
     * Type: subscribtion or oneclick
     * Example: oneclick
     *
     * @var string
     */
    protected $aliasmode;

    /**
     * Description: Reference transaction to be edited
     * Type: String (32 max)
     * Example: AB13243543
     *
     * @var string
     */
    protected $transactionid;

    /**
     * Description: Card number. Do not store this property
     * Type: String (16)
     * Example: 1111222233334444
     *
     * @var string
     */
    protected $cardcode;

    /**
     * Description: Secured card number
     * Type: String (16)
     * Example: ************4444
     *
     * @var string
     */
    protected $cardcodeSafe;

    /**
     * Description: Validity date. Do not store this property
     * Type: YY-MM
     * Example: 13-05
     *
     * @var string
     */
    protected $cardvaliditydate;

    /**
     * Description: Visual cryptogram. Do not store this property
     * Type: Integer
     * Example: 123
     *
     * @var int
     */
    protected $cardcvv;

    /**
     * Description: Full name shown on the cardholder's card. Do not store this property
     * Type: String (255 max)
     * Example: John Doe
     *
     * @var string
     */
    protected $cardfullname;

    /**
     * @var string
     */
    protected $cardfullnameSafe;

    /**
     * Description: Transaction action code
     * Type: Integer
     * Example: 0000
     *
     * @var int
     */
    protected $execcode;

    /**
     * Description: Description related to the transaction code
     * Type: String (510 max)
     * Example: OperationSucceed
     *
     * @var string
     */
    protected $message;

    /**
     * Description: The text appearing on the cardholder's bank statement in case of a successful transaction
     * Type: String (32 max)
     * Example: site.com
     *
     * @var string
     */
    protected $descriptor;

    /**
     * @return string
     */
    public function getOperationtype()
    {
        return $this->operationtype;
    }

    /**
     * @param string $operationtype
     */
    public function setOperationtype($operationtype)
    {
        $this->operationtype = $operationtype;
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
     * @return string
     */
    public function getOrderid()
    {
        return $this->orderid;
    }

    /**
     * @param string $orderid
     */
    public function setOrderid($orderid)
    {
        $this->orderid = $orderid;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCardtype()
    {
        return $this->cardtype;
    }

    /**
     * @param string $cardtype
     */
    public function setCardtype($cardtype)
    {
        $this->cardtype = $cardtype;
    }

    /**
     * @return string
     */
    public function getClientident()
    {
        return $this->clientident;
    }

    /**
     * @param string $clientident
     */
    public function setClientident($clientident)
    {
        $this->clientident = $clientident;
    }

    /**
     * @return string
     */
    public function getClientemail()
    {
        return $this->clientemail;
    }

    /**
     * @param string $clientemail
     */
    public function setClientemail($clientemail)
    {
        $this->clientemail = $clientemail;
    }

    /**
     * @return string
     */
    public function getClientaddress()
    {
        return $this->clientaddress;
    }

    /**
     * @param string $clientaddress
     */
    public function setClientaddress($clientaddress)
    {
        $this->clientaddress = $clientaddress;
    }

    /**
     * @return string
     */
    public function getClientdob()
    {
        return $this->clientdob;
    }

    /**
     * @param string $clientdob
     */
    public function setClientdob($clientdob)
    {
        $this->clientdob = $clientdob;
    }

    /**
     * @return string
     */
    public function getClientreferer()
    {
        return $this->clientreferer;
    }

    /**
     * @param string $clientreferer
     */
    public function setClientreferer($clientreferer)
    {
        $this->clientreferer = $clientreferer;
    }

    /**
     * @return string
     */
    public function getClientuseragent()
    {
        return $this->clientuseragent;
    }

    /**
     * @param string $clientuseragent
     */
    public function setClientuseragent($clientuseragent)
    {
        $this->clientuseragent = $clientuseragent;
    }

    /**
     * @return string
     */
    public function getClientip()
    {
        return $this->clientip;
    }

    /**
     * @param string $clientip
     */
    public function setClientip($clientip)
    {
        $this->clientip = $clientip;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getCreatealias()
    {
        return $this->createalias;
    }

    /**
     * @param string $createalias
     */
    public function setCreatealias($createalias)
    {
        $this->createalias = $createalias ? 'yes' : 'no';
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAliasmode()
    {
        return $this->aliasmode;
    }

    /**
     * @param string $aliasmode
     */
    public function setAliasmode($aliasmode)
    {
        $this->aliasmode = $aliasmode;
    }

    /**
     * @return string
     */
    public function getTransactionid()
    {
        return $this->transactionid;
    }

    /**
     * @param string $transactionid
     */
    public function setTransactionid($transactionid)
    {
        $this->transactionid = $transactionid;
    }

    /**
     * @return string
     */
    public function getCardcode()
    {
        return $this->cardcode;
    }

    /**
     * @param string $cardcode
     */
    public function setCardcode($cardcode)
    {
        $this->cardcode = $cardcode;
        
        $this->setCardcodeSafe($cardcode);
    }

    /** 
     * @return string
     */
    public function getCardcodeSafe()
    {
        return $this->cardcodeSafe;
    }
    
    protected function setCardcodeSafe($cardcode)
    {
        $cardcodeLength = mb_strlen($cardcode);
        
        $this->cardcodeSafe = str_repeat('*', $cardcodeLength - 4).substr($cardcode, -4);   
    }

    /**
     * @return string
     */
    public function getCardfullnameSafe()
    {
        return $this->cardfullnameSafe;
    }

    protected function setCardfullnameSafe($cardfullname)
    {
        $cardfullnameLength = mb_strlen($cardfullname);

        $this->cardfullnameSafe = str_repeat('*', $cardfullnameLength - 4).substr($cardfullname, -4);
    }

    /**
     * @return string
     */
    public function getCardvaliditydate()
    {
        return $this->cardvaliditydate;
    }

    /**
     * @param string $cardvaliditydate
     */
    public function setCardvaliditydate($cardvaliditydate)
    {
        $this->cardvaliditydate = $cardvaliditydate;
    }

    /**
     * @return int
     */
    public function getCardcvv()
    {
        return $this->cardcvv;
    }

    /**
     * @param int $cardcvv
     */
    public function setCardcvv($cardcvv)
    {
        $this->cardcvv = $cardcvv;
    }

    /**
     * @return string
     */
    public function getCardfullname()
    {
        return $this->cardfullname;
    }

    /**
     * @param string $cardfullname
     */
    public function setCardfullname($cardfullname)
    {
        $this->cardfullname = $cardfullname;
        
        $this->setCardfullnameSafe($cardfullname);
    }

    /**
     * @return int
     */
    public function getExeccode()
    {
        return $this->execcode;
    }

    /**
     * @param int $execcode
     */
    public function setExeccode($execcode)
    {
        $this->execcode = $execcode;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }

    /**
     * @param string $descriptor
     */
    public function setDescriptor($descriptor)
    {
        $this->descriptor = $descriptor;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return 
            in_array($offset, $this->getSupportedArrayFields()) &&     
            property_exists($this, strtolower($offset))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ?
            $this->{strtolower($offset)} :
            null
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (false == $this->offsetExists($offset)) {
            throw new InvalidArgumentException(sprintf('Unsupported offset given %s.', $offset));
        }
        
        $this->{strtolower($offset)} = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->{strtolower($offset)} = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $array = array();
        foreach ($this->getSupportedArrayFields() as $name) {
            $array[$name] = $this[$name];
        }

        return new \ArrayIterator(array_filter($array));
    }

    /**
     * @return array
     */
    protected function getSupportedArrayFields()
    {
        $rc = new \ReflectionClass(__CLASS__);

        $fields = array();
        foreach ($rc->getProperties() as $rp) {
            $fields[] = strtoupper($rp->getName());
        }

        return $fields;
    }
}