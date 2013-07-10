<?php
namespace Payum\Payex\Model;

/**
 * @link http://www.payexpim.com/technical-reference/pxorder/initialize8/
 * @link http://www.payexpim.com/technical-reference/pxorder/complete-2/
 * @link http://www.payexpim.com/technical-reference/pxagreement/autopay/
 * @link http://www.payexpim.com/technical-reference/pxrecurring/pxrecurring-check/
 * @link http://www.payexpim.com/technical-reference/pxrecurring/pxrecurring-start/
 */
class PaymentDetails implements \ArrayAccess, \IteratorAggregate
{
    /**
     * SALE | AUTHORIZATION
     * If AUTHORIZATION is submitted, this indicates that the order will be a 2-phased transaction if the payment method supports it.
     * 
     * @var string
     */
    protected $purchaseOperation;
    
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
    protected $orderId;

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
     * 0=Sale, 1=Initialize, 2=Credit, 3=Authorize, 4=Cancel,5=Failure,6=Capture (This field needs to be validated by the merchant to verify wether the transaction was successful or not).
     * 
     * @var int 
     */
    protected $transactionStatus;

    /**
     * Returns the transaction number if the transaction is successful. 
     * This is useful for support reference as this is the number available in the merchant admin view and also the transaction number presented to the end user.
     * 
     * @var string
     */
    protected $transactionNumber;

    /**
     * @deprecated Deprecated, do not store or use this response parameter. Use transactionNumber instead, We are keeping it because api still returns this value.
     * 
     * @var string
     */
    protected $transactionRef;

    /**
     * This returns the productNumber supplied by the merchant when the order was created, enabling the merchant to link the return data from PayEx with their local orderID
     * 
     * @var string
     */
    protected $productId;

    /**
     * Returns the payment method used to pay for this transaction (PX, VISA, MC, DD, INVOICE etc)
     * 
     * @var string
     */
    protected $paymentMethod;

    /**
     * Returns the amount credited the merchant The value is returned as sent in. Example: 1000 = 10.00 NOK
     * 
     * @var int
     */
    protected $amount;

    /**
     * Returns false the first time complete is called successfully, but if complete is ever called with the same orderRef the returned value will be true
     * 
     * @var bool
     */
    protected $alreadyCompleted;

    /**
     * Returns the stopdate if the purchase is a subscription
     * 
     * @var string
     */
    protected $stopDate;

    /**
     * Returns the client’s GSM number, if the paymentmethod is CPA. Else the parameter is blank
     * 
     * @var string
     */
    protected $clientGsmNumber;

    /**
     * Returns the Status of the order0 = The order is completed (a purchase has been done, but check the transactionStatus to see the result).
     * 1 = The order is processing. The customer has not started the purchase. PxOrder.Complete can return orderStatus 1 for 2 weeks after PxOrder.Initialize is called. Afterwards the orderStatus will be set to 2
     * 2 = No order or transaction is found
     * 
     * @var string
     */
    protected $orderStatus;

    /**
     * Expire date of the agreement
     *
     * @var string
     */
    protected $paymentMethodExpireDate;

    /**
     * Returns a hash of the credit card number
     *
     * @var string
     */
    protected $BankHash;

    /**
     * Returns the masked credit card number. Only returned for Agreements where the Initialize parameter View is set to CC
     * 
     * @var string
     */
    protected $maskedNumber;

    /**
     * If a value is returned it will be either “None” or “3DSecure”
     * 
     * @var string
     */
    protected $AuthenticatedStatus;

    /**
     * If authenticatedStatus returns “3DSecure”, the following values is returned:Y = 3DSecure verification is OK and the cardholder is authenticated by the acquiring bank.
     * 
     * @var string
     */
    protected $AuthenticatedWith;

    /**
     * Returns true if the transaction has triggered the fraud detection module
     * 
     * @var bool
     */
    protected $fraudData;

    /**
     * Only used with Financing and PayPal payment methods. Returns true if we do not know the status of the transaction from third party, transactionStatus will be init
     * 
     * @var bool
     */
    protected $pending;

    /**
     * Returns a error code of why the transaction failed
     * 
     * @var string
     */
    protected $transactionErrorCode;

    /**
     * Returns a description of why the transaction failed
     * 
     * @var string
     */
    protected $transactionErrorDescription;

    /**
     * Returns the thirdPartyError of why the transaction failed. We recommend all merchants to log this error with your orders. This info is very useful when contacting our support team
     * 
     * @var string
     */
    protected $transactionThirdPartyError;

    /**
     * System Trace Authid Number
     * 
     * @var int
     */
    protected $stan;

    /**
     * Terminal Id
     * 
     * @var int
     */
    protected $terminalId;

    /**
     * Valid timestamp according to ISO 8601
     * 
     * @var string
     */
    protected $TransactionTime;

    /**
     * Returns the name of the parameter that contains invalid data.
     * 
     * @var string
     */
    protected $paramName;

    /**
     * @var string
     */
    protected $Csid;

    /**
     * @var string
     */
    protected $thirdPartySubError;

    /**
     * @var string
     */
    protected $clientAccount;

    /**
     * @var array
     */
    protected $errorDetails;

    /**
     * @var string
     */
    protected $transactionFailedReason;

    /**
     * This field is not come from the api. we add it to diff ordinary payment and autopay.
     * 
     * @var bool
     */
    protected $autoPay;

    /**
     * This field is not come from the api. we add it to diff ordinary payment and recurring.
     *
     * @var bool
     */
    protected $recurring;

    /**
     * @var string
     */
    protected $recurringRef;

    /**
     * This is the start date of the recurring payment. This payment will not be started before the start date are older than current date. Can be left blank if current datetime are to be used.Format: (yyyy-MM-dd hh:mm:ss)
     * 
     * @var string 
     */
    protected $startDate;

    /**
     * Describes the length of the period. The following parameters are valid; 1 = Hours, 2 = Daily, 3 = Weekly, 4 = Monthly, 5 = Quarterly and 6 = Yearly. All parameters but Hours, where the agreement supports CPA, will be moved so it starts within 08:00 – 21:00 timeline. This is to prevent sending out CPA messages in the night.
     * 
     * @var int
     */
    protected $periodType;

    /**
     * This is the number of hours (minimum 24) if periodType are set to hours(1). For all other periodType settings, this field must be set to 0.
     * 
     * @var int
     */
    protected $period;

    /**
     * Desides how long in advance of the recurring payment the alert will be sent out. This period is given in hours. If this field is set to 0 no alert will be sent out.
     * 
     * @var int
     */
    protected $alertPeriod;

    /**
     * Returns the status of the recurring payment.
     * 
     * @var string
     */
    protected $recurringStatus;

    /**
     * Returns the renewal date of the agreement.
     * 
     * @var string
     */
    protected $renewalDate;
    
    public function __construct()
    {
        $this->priceArgList = '';
        $this->vat = 0;
        $this->clientIdentifier = '';
        $this->additionalValues = '';
        $this->agreementRef = '';
        $this->cancelUrl = '';
        $this->clientLanguage = '';
        $this->recurring = false;
        $this->autoPay = false;
        $this->alertPeriod = 0;
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
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
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
     * @return int
     */
    public function getTransactionStatus()
    {
        return $this->transactionStatus;
    }

    /**
     * @param int $transactionStatus
     */
    public function setTransactionStatus($transactionStatus)
    {
        $this->transactionStatus = $transactionStatus;
    }

    /**
     * @return string
     */
    public function getTransactionNumber()
    {
        return $this->transactionNumber;
    }

    /**
     * @param string $transactionNumber
     */
    public function setTransactionNumber($transactionNumber)
    {
        $this->transactionNumber = $transactionNumber;
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
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
     * @return boolean
     */
    public function getAlreadyCompleted()
    {
        return $this->alreadyCompleted;
    }

    /**
     * @param boolean $alreadyCompleted
     */
    public function setAlreadyCompleted($alreadyCompleted)
    {
        $this->alreadyCompleted = $alreadyCompleted;
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
    public function getClientGsmNumber()
    {
        return $this->clientGsmNumber;
    }

    /**
     * @param string $clientGsmNumber
     */
    public function setClientGsmNumber($clientGsmNumber)
    {
        $this->clientGsmNumber = $clientGsmNumber;
    }

    /**
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * @param string $orderStatus
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * @return string
     */
    public function getPaymentMethodExpireDate()
    {
        return $this->paymentMethodExpireDate;
    }

    /**
     * @param string $paymentMethodExpireDate
     */
    public function setPaymentMethodExpireDate($paymentMethodExpireDate)
    {
        $this->paymentMethodExpireDate = $paymentMethodExpireDate;
    }

    /**
     * @return string
     */
    public function getBankHash()
    {
        return $this->BankHash;
    }

    /**
     * @param string $BankHash
     */
    public function setBankHash($BankHash)
    {
        $this->BankHash = $BankHash;
    }

    /**
     * @return string
     */
    public function getMaskedNumber()
    {
        return $this->maskedNumber;
    }

    /**
     * @param string $maskedNumber
     */
    public function setMaskedNumber($maskedNumber)
    {
        $this->maskedNumber = $maskedNumber;
    }

    /**
     * @return string
     */
    public function getAuthenticatedStatus()
    {
        return $this->AuthenticatedStatus;
    }

    /**
     * @param string $AuthenticatedStatus
     */
    public function setAuthenticatedStatus($AuthenticatedStatus)
    {
        $this->AuthenticatedStatus = $AuthenticatedStatus;
    }

    /**
     * @return string
     */
    public function getAuthenticatedWith()
    {
        return $this->AuthenticatedWith;
    }

    /**
     * @param string $AuthenticatedWith
     */
    public function setAuthenticatedWith($AuthenticatedWith)
    {
        $this->AuthenticatedWith = $AuthenticatedWith;
    }

    /**
     * @return boolean
     */
    public function getFraudData()
    {
        return $this->fraudData;
    }

    /**
     * @param boolean $fraudData
     */
    public function setFraudData($fraudData)
    {
        $this->fraudData = $fraudData;
    }

    /**
     * @return boolean
     */
    public function getPending()
    {
        return $this->pending;
    }

    /**
     * @param boolean $pending
     */
    public function setPending($pending)
    {
        $this->pending = $pending;
    }

    /**
     * @return string
     */
    public function getTransactionErrorCode()
    {
        return $this->transactionErrorCode;
    }

    /**
     * @param string $transactionErrorCode
     */
    public function setTransactionErrorCode($transactionErrorCode)
    {
        $this->transactionErrorCode = $transactionErrorCode;
    }

    /**
     * @return string
     */
    public function getTransactionErrorDescription()
    {
        return $this->transactionErrorDescription;
    }

    /**
     * @param string $transactionErrorDescription
     */
    public function setTransactionErrorDescription($transactionErrorDescription)
    {
        $this->transactionErrorDescription = $transactionErrorDescription;
    }

    /**
     * @return string
     */
    public function getTransactionThirdPartyError()
    {
        return $this->transactionThirdPartyError;
    }

    /**
     * @param string $transactionThirdPartyError
     */
    public function setTransactionThirdPartyError($transactionThirdPartyError)
    {
        $this->transactionThirdPartyError = $transactionThirdPartyError;
    }

    /**
     * @return int
     */
    public function getStan()
    {
        return $this->stan;
    }

    /**
     * @param int $stan
     */
    public function setStan($stan)
    {
        $this->stan = $stan;
    }

    /**
     * @return int
     */
    public function getTerminalId()
    {
        return $this->terminalId;
    }

    /**
     * @param int $terminalId
     */
    public function setTerminalId($terminalId)
    {
        $this->terminalId = $terminalId;
    }

    /**
     * @return string
     */
    public function getTransactionTime()
    {
        return $this->TransactionTime;
    }

    /**
     * @param string $TransactionTime
     */
    public function setTransactionTime($TransactionTime)
    {
        $this->TransactionTime = $TransactionTime;
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
     * @return boolean
     */
    public function getAutoPay()
    {
        return $this->autoPay;
    }

    /**
     * @param boolean $autoPay
     */
    public function setAutoPay($autoPay)
    {
        $this->autoPay = $autoPay;
    }

    /**
     * @return boolean
     */
    public function getRecurring()
    {
        return $this->recurring;
    }

    /**
     * @param boolean $recurring
     */
    public function setRecurring($recurring)
    {
        $this->recurring = $recurring;
    }

    /**
     * @return string
     */
    public function getRecurringRef()
    {
        return $this->recurringRef;
    }

    /**
     * @param string $recurringRef
     */
    public function setRecurringRef($recurringRef)
    {
        $this->recurringRef = $recurringRef;
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
     * @return int
     */
    public function getPeriodType()
    {
        return $this->periodType;
    }

    /**
     * @param int $periodType
     */
    public function setPeriodType($periodType)
    {
        $this->periodType = $periodType;
    }

    /**
     * @return int
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param int $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @return int
     */
    public function getAlertPeriod()
    {
        return $this->alertPeriod;
    }

    /**
     * @param int $alertPeriod
     */
    public function setAlertPeriod($alertPeriod)
    {
        $this->alertPeriod = $alertPeriod;
    }

    /**
     * @return string
     */
    public function getRecurringStatus()
    {
        return $this->recurringStatus;
    }

    /**
     * @param string $recurringStatus
     */
    public function setRecurringStatus($recurringStatus)
    {
        $this->recurringStatus = $recurringStatus;
    }

    /**
     * @return string
     */
    public function getRenewalDate()
    {
        return $this->renewalDate;
    }

    /**
     * @param string $renewalDate
     */
    public function setRenewalDate($renewalDate)
    {
        $this->renewalDate = $renewalDate;
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