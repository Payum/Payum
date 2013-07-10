<?php
namespace Payum\Payex\Model;

/**
 * @link http://www.payexpim.com/technical-reference/pxagreement/createagreement3/
 * @link http://www.payexpim.com/technical-reference/pxagreement/check/
 */
class AgreementDetails implements \ArrayAccess, \IteratorAggregate
{
    /**
     * A reference that links this agreement to something the merchant takes money for.
     * 
     * @var string
     */
    protected $merchantRef;

    /**
     * A short description about this agreement. 
     * This will show up on the client admin page so that the client gets info about the agreement. 
     * It will also show on the web page where the client verifies the agreement.
     * 
     * @var string
     */
    protected $description;

    /**
     * SALE | AUTHORIZATION
     * If AUTHORIZATION is submitted, this indicates that the order will be a 2-phased transaction if the payment method supports it. 
     * This is the value that will be used in AutoPay if the purchaseOperation parameter is left empty in the AutoPay call
     * 
     * @var string
     */
    protected $purchaseOperation;

    /**
     * One single transaction can never be greater than this amount. 
     * Give yourself some leeway here so you do not have to make new agreements if you decide to raise your price. 
     * The value is passed as an integer multiplied by 100. Example: 900000 = 9000.00 NOK
     * 
     * @var int
     */
    protected $maxAmount;

    /**
     * If this parameter is set there is a start date on this agreement and the agreement don’t start wotking before this date/time.Format: (yyyy-MM-dd hh:mm:ss)
     * 
     * @var string
     */
    protected $startDate;

    /**
     * If this parameter is set there is a stop date on this agreement and the agreement will not work after this date/time. If there are a recurring autopay using this agreement this will have to be deleted when the stop date occurs.Format: (yyyy-MM-dd hh:mm:ss)
     * 
     * @var string
     */
    protected $stopDate;

    /**
     * A more informative error code which indicates the result of the request. Returns OK if request is successful.
     * 
     * @var string
     */
    protected $errorCode;

    /**
     * A literal description explaining the result. Returns OK if request is successful.
     * 
     * @var string
     */
    protected $errorDescription;

    /**
     * @var string
     */
    protected $thirdPartySubError;

    /**
     * Returns the name of the parameter that contains invalid data.
     * 
     * @var string
     */
    protected $paramName;

    /**
     * Returns the error code received from third party (if returned).
     * 
     * @var string
     */
    protected $thirdPartyError;

    /**
     * Reference to the created agreement.
     * 
     * @var string
     */
    protected $agreementRef;
    
    /**
     * Returns the status of an agreement.
     * NotVerified = 0
     * Verified = 1
     * Deleted = 2
     * 
     * @var int
     */
    protected $agreementStatus;

    public function __construct()
    {
        //set optional fields so the api will not fail.
        $this->purchaseOperation = '';
        $this->startDate = '';
        $this->stopDate = '';
    }

    /**
     * @return string
     */
    public function getMerchantRef()
    {
        return $this->merchantRef;
    }

    /**
     * @param string $merchantRef
     */
    public function setMerchantRef($merchantRef)
    {
        $this->merchantRef = $merchantRef;
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
    public function getPurchaseOperation()
    {
        return $this->purchaseOperation;
    }

    /**
     * @param string $purchaseOperation
     */
    public function setPurchaseOperation($purchaseOperation)
    {
        $this->purchaseOperation = $purchaseOperation;
    }

    /**
     * @return int
     */
    public function getMaxAmount()
    {
        return $this->maxAmount;
    }

    /**
     * @param int $maxAmount
     */
    public function setMaxAmount($maxAmount)
    {
        $this->maxAmount = $maxAmount;
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return string
     */
    public function getStopDate()
    {
        return $this->stopDate;
    }

    /**
     * @param string $stopDate
     */
    public function setStopDate($stopDate)
    {
        $this->stopDate = $stopDate;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param string $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }

    /**
     * @param string $errorDescription
     */
    public function setErrorDescription($errorDescription)
    {
        $this->errorDescription = $errorDescription;
    }

    /**
     * @return string
     */
    public function getParamName()
    {
        return $this->paramName;
    }

    /**
     * @param string $paramName
     */
    public function setParamName($paramName)
    {
        $this->paramName = $paramName;
    }

    /**
     * @return string
     */
    public function getThirdPartyError()
    {
        return $this->thirdPartyError;
    }

    /**
     * @param string $thirdPartyError
     */
    public function setThirdPartyError($thirdPartyError)
    {
        $this->thirdPartyError = $thirdPartyError;
    }

    /**
     * @return string
     */
    public function getAgreementRef()
    {
        return $this->agreementRef;
    }

    /**
     * @param string $agreementRef
     */
    public function setAgreementRef($agreementRef)
    {
        $this->agreementRef = $agreementRef;
    }

    /**
     * @return int
     */
    public function getAgreementStatus()
    {
        return $this->agreementStatus;
    }

    /**
     * @param int $agreementStatus
     */
    public function setAgreementStatus($agreementStatus)
    {
        $this->agreementStatus = $agreementStatus;
    }
    
    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return
            in_array($offset, $this->getSupportedArrayFields()) &&
            property_exists($this, $offset)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->$offset : null;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->$offset = null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $array = array();
        foreach ($this->getSupportedArrayFields() as $name) {
            $array[$name] = $this[$name];
        }

        return new \ArrayIterator(array_filter($array, function($value) {
            return false === is_null($value);
        }));
    }

    /**
     * @return array
     */
    protected function getSupportedArrayFields()
    {
        $rc = new \ReflectionClass(__CLASS__);

        $fields = array();
        foreach ($rc->getProperties() as $rp) {
            $fields[] = $rp->getName();
        }

        return $fields;
    }
}