<?php
namespace Payum\Payex\Model;

use Payum\Exception\InvalidArgumentException;

/**
 * TODO add one phase payment fields.
 * 
 * @link http://www.payexpim.com/quick-guide/initialize/
 */
class PaymentDetails implements \ArrayAccess, \IteratorAggregate
{
    /**
     * This parameter determines the amount you would like to charge incl. VAT. 
     * The value is passed as an integer multiplied by 100. E.g. 100.00 NOK = 10000, 59.99 SEK = 5999, 400 ISK = 40000.
     * Set to 0 if priceArgList is used.
     * 
     * @var int
     */
    protected $price;

    /**
     * The value is passed as an integer multiplied by 100. Example: 1200 = 12.00 NOK. 
     * Set this parameter to [blank] if Price is submitted.
     * Comma separated field with name=value data for specifying prices for several payment methods. (Example: PX=2000,CPA=2500,VISA=5000,MC=5000)
     * 
     * @var string
     */
    protected $priceArgList;

    /**
     * Set to your desired currency.
     * 
     * @var string
     */
    protected $currency;

    /**
     * If the price includes vat, this may be displayed for the user using this parameter. 2500 = 25%.
     * 
     * @var int
     */
    protected $vat;

    /**
     * Use this to send in your local ID identifying this particular order. 
     * Using an unique orderID is strongly recommended. 
     * If you use invoice as payment method this string is restricted to these characters [a-zA-Z0-9]
     *
     * @var string
     */
    protected $orderID;

    /**
     * Merchant product number/reference for this specific product. 
     * We recommend that only the characters A-Z and 0-9 are used in this parameter.
     *
     * @var string
     */
    protected $productNumber;

    /**
     * Merchant’s description of the product.
     * 
     * @var string
     */
    protected $description;

    /**
     * Here you send in the customers IP address.
     *
     * @var string
     */
    protected $clientIPAddress;

    /**
     * The information in this field is only used if you are implementing Credit Card in the direct model. 
     * It is used for 3D-secure verification. Send in your customers user agent.
     * USERAGENT=value
     *
     * @var string
     */
    protected $clientIdentifier;

    /**
     * Set this parameter to PAYMENTMENU=TRUE if you’re using more than one payment method, else leave empty.
     * 
     * @var string 
     */
    protected $additionalValues;

    /**
     * A string identifying the full URL for the page the user will be redirected to after a successful purchase. 
     * We will add orderRef to the existing query, and if no query is supplied to the URL, then the query will be added.
     *
     * @var string
     */
    protected $returnUrl;

    /**
     * Set this parameter to PX
     * 
     * @var string 
     */
    protected $view;

    /**
     * Specify the agreementRef (from PxAgreement.CreateAgreement2) to open for recurring payments. 
     * The following payments should be performed by using PxAgreement.Autopay. 
     * Set to blank if you don’t want this functionality. Note: The customer must be informed of recurring payments.
     * 
     * @var string
     */
    protected $agreementRef;

    /**
     * A string identifying the full URL for the page the user will be redirected to after a successful purchase.
     * We will add orderRef to the existing query, and if no query is supplied to the URL, then the query will be added.
     *
     * @var string
     */
    protected $cancelUrl;

    /**
     * The language used in the redirect purchase dialog with the client. 
     * Available languages depend on the merchant configuration. 
     * Supported languages: nb-NO,da-DK,en-US,sv-SE,es-ES,de-DE,fi-FI,fr-FR,pl-PL,cs-CZ,hu-HU
     * If no language is specified, the default language for client UI is used.
     * 
     * @var string
     */
    protected $clientLanguage;

    /**
     * Indicates the result of the request. Returns OK if request is successful.
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
     * Returns the error code received from third party (not available for all payment methods).
     * 
     * @var string
     */
    protected $thirdPartyError;

    /**
     * This parameter is only returned if the parameter is successful, and returns a 32bit, hexadecimal value (Guid) identifying the orderRef.
     * Example: 8e96e163291c45f7bc3ee998d3a8939c
     * 
     * @var string
     */
    protected $orderRef;

    /**
     * Dynamic URL to send the end user to, when using redirect model.
     * 
     * @var string
     */
    protected $redirectUrl;

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getPriceArgList()
    {
        return $this->priceArgList;
    }

    /**
     * @param string $priceArgList
     */
    public function setPriceArgList($priceArgList)
    {
        $this->priceArgList = $priceArgList;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param int $vat
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    }

    /**
     * @return string
     */
    public function getOrderID()
    {
        return $this->orderID;
    }

    /**
     * @param string $orderID
     */
    public function setOrderID($orderID)
    {
        $this->orderID = $orderID;
    }

    /**
     * @return string
     */
    public function getProductNumber()
    {
        return $this->productNumber;
    }

    /**
     * @param string $productNumber
     */
    public function setProductNumber($productNumber)
    {
        $this->productNumber = $productNumber;
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
    public function getClientIPAddress()
    {
        return $this->clientIPAddress;
    }

    /**
     * @param string $clientIPAddress
     */
    public function setClientIPAddress($clientIPAddress)
    {
        $this->clientIPAddress = $clientIPAddress;
    }

    /**
     * @return string
     */
    public function getClientIdentifier()
    {
        return $this->clientIdentifier;
    }

    /**
     * @param string $clientIdentifier
     */
    public function setClientIdentifier($clientIdentifier)
    {
        $this->clientIdentifier = $clientIdentifier;
    }

    /**
     * @return string
     */
    public function getAdditionalValues()
    {
        return $this->additionalValues;
    }

    /**
     * @param string $additionalValues
     */
    public function setAdditionalValues($additionalValues)
    {
        $this->additionalValues = $additionalValues;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param string $view
     */
    public function setView($view)
    {
        $this->view = $view;
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
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * @param string $cancelUrl
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }

    /**
     * @return string
     */
    public function getClientLanguage()
    {
        return $this->clientLanguage;
    }

    /**
     * @param string $clientLanguage
     */
    public function setClientLanguage($clientLanguage)
    {
        $this->clientLanguage = $clientLanguage;
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
    public function getOrderRef()
    {
        return $this->orderRef;
    }

    /**
     * @param string $orderRef
     */
    public function setOrderRef($orderRef)
    {
        $this->orderRef = $orderRef;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param string $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
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
        if (false == $this->offsetExists($offset)) {
            throw new InvalidArgumentException(sprintf('Unsupported offset given %s.', $offset));
        }

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
            $fields[] = $rp->getName();
        }

        return $fields;
    }
}