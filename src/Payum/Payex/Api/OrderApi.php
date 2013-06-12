<?php
namespace Payum\Payex\Api;

use Payum\Exception\InvalidArgumentException;

class OrderApi
{
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
        
        unset($result['status']);
        
        foreach ($inputResult['status'] as $name => $value) {
            $result[$name] = $value;
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