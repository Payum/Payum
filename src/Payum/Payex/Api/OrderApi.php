<?php
namespace Payum\Payex\Api;

use Payum\Exception\InvalidArgumentException;

class OrderApi
{
    const PURCHASEOPERATION_SALE = 'SALE';

    /**
     * If AUTHORIZATION is submitted, this indicates that the order will be a 2-phased transaction if the payment method supports it.
     */
    const PURCHASEOPERATION_AUTHORIZATION = 'AUTHORIZATION';
    
    const VIEW_CREDITCARD = 'CREDITCARD';

    const VIEW_MICROACCOUNT = 'MICROACCOUNT';

    const VIEW_DIRECTDEBIT = 'DIRECTDEBIT';

    /**
     * Norwegian and Swedish overcharged SMS
     */
    const VIEW_CPA = 'CPA';

    /**
     * Overcharged call
     */
    const VIEW_IVR = 'IVR';

    /**
     * Value code
     */
    const VIEW_EVC = 'EVC';

    const VIEW_INVOICE = 'INVOICE';

    const VIEW_LOAN = 'LOAN';

    /**
     * Gift card / generic card
     */
    const VIEW_GC = 'GC';

    /**
     * Credit account
     */
    const VIEW_CA = 'GC';

    /**
     * PayPal transactions
     */
    const VIEW_PAYPAL = 'PAYPAL';

    const VIEW_FINANCING = 'FINANCING';

    /**
     * Returns OK if request is successful.
     */
    const ERRORCODE_OK = 'OK';

    const TRANSACTIONSTATUS_SALE = 0;

    const TRANSACTIONSTATUS_INITIALIZE = 1;

    const TRANSACTIONSTATUS_CREDIT = 2;

    const TRANSACTIONSTATUS_AUTHORIZE = 3;

    const TRANSACTIONSTATUS_CANCEL = 4;

    const TRANSACTIONSTATUS_FAILURE = 5;

    const TRANSACTIONSTATUS_CAPTURE = 6;

    /**
     * Returns the Status of the order0 = The order is completed (a purchase has been done, but check the transactionStatus to see the result).
     */
    const ORDERSTATUS_COMPLETED = 0;

    /**
     * 1 = The order is processing. The customer has not started the purchase. PxOrder.Complete can return orderStatus 1 for 2 weeks after PxOrder.Initialize is called. Afterwards the orderStatus will be set to 2
     */
    const ORDERSTATUS_PROCESSING = 1;

    /**
     * 2 = No order or transaction is found
     */
    const ORDERSTATUS_NOT_FOUND = 2;

    /**
     * @var SoapClientFactory
     */
    protected $clientFactory;
    
    /**
     * @var array
     */
    protected $options;

    /**
     * @param SoapClientFactory $clientFactory
     * @param array $options
     *
     * @throws \Payum\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(SoapClientFactory $clientFactory, array $options) 
    {
        $this->clientFactory = $clientFactory;
        $this->options = $options;
        
        if (true == empty($this->options['accountNumber'])) {
            throw new InvalidArgumentException('The accountNumber option must be set.');
        }
        
        if (true == empty($this->options['encryptionKey'])) {
            throw new InvalidArgumentException('The encryptionKey option must be set.');
        }

        if (false == is_bool($this->options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
    }

    /**
     * @link http://www.payexpim.com/technical-reference/pxorder/initialize8/
     * 
     * @var array $parameters
     * 
     * @return array
     */
    public function initialize(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['accountNumber'];
        
        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'purchaseOperation',
            'price',
            'priceArgList',
            'currency',
            'vat',
            'orderID',
            'productNumber',
            'description',
            'clientIPAddress',
            'clientIdentifier',
            'additionalValues',
            'externalID',
            'returnUrl',
            'view',
            'agreementRef',
            'cancelUrl',
            'clientLanguage'
        ));
        
        $client = $this->clientFactory->createWsdlClient($this->getPxOrderWsdl());

        $response = @$client->Initialize8($parameters);

        $result = $this->convertSimpleXmlToArray(new \SimpleXMLElement($response->Initialize8Result));
        $result = $this->normalizeStatusFields($result);
        $result = $this->removeHeader($result);
        
        return $result;
    }

    /**
     * @link http://www.payexpim.com/technical-reference/pxorder/complete-2/
     * 
     * @param array $parameters
     * 
     * @return array
     */
    public function complete(array $parameters)
    {
        $parameters['accountNumber'] = $this->options['accountNumber'];

        $parameters['hash'] = $this->calculateHash($parameters, array(
            'accountNumber',
            'orderRef',
        ));

        $client = $this->clientFactory->createWsdlClient($this->getPxOrderWsdl());

        $response = @$client->Complete($parameters);

        $result = $this->convertSimpleXmlToArray(new \SimpleXMLElement($response->CompleteResult));
        $result = $this->normalizeStatusFields($result);
        $result = $this->removeHeader($result);

        return $result;
    }

    /**
     * @param array $parameters
     * @param array $parametersKeys
     * 
     * @return string
     */
    protected function calculateHash(array $parameters, array $parametersKeys)
    {
        $orderedParameters = array();
        foreach ($parametersKeys as $parametersKey) {
            if (false == isset($parameters[$parametersKey])) {
                //TODO exception?
                continue;
            }
            
            $orderedParameters[$parametersKey] = $parameters[$parametersKey];
        }
        
        return md5(trim(implode("", $orderedParameters)) . $this->options['encryptionKey']);
    }

    /**
     * @return string
     */
    protected function getPxOrderWsdl()
    {
        return $this->options['sandbox'] ? 
            'https://test-external.payex.com/pxorder/pxorder.asmx?wsdl' : 
            'https://external.payex.com/pxorder/pxorder.asmx?wsdl'
        ;
    }

    /**
     * @param \SimpleXMLElement $element
     * 
     * @return array
     */
    protected function convertSimpleXmlToArray(\SimpleXMLElement $element)
    {
        return json_decode(
            json_encode((array) $element), 
            $assoc = true
        );
    }

    /**
     * @param array $inputResult
     * 
     * @return array
     */
    protected function normalizeStatusFields(array $inputResult)
    {
        $result = $inputResult;

        if (array_key_exists('status', $result) && is_array($result['status'])) {
            unset($result['status']);
            
            foreach ($inputResult['status'] as $name => $value) {
                $result[$name] = $value;
            }
        }
        
        if (array_key_exists('description', $result)) {
            $result['errorDescription'] = $result['description'];
            
            unset($result['description']);
        }
        
        return $result;
    }

    /**
     * @param array $inputResult
     * 
     * @return array 
     */
    protected function removeHeader(array $inputResult)
    {
        $result = $inputResult;
        
        unset($result['header']);
        
        return $result;
    }
}