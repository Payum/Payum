<?php
namespace Payum\Payex\Api;

use Payum\Core\Exception\InvalidArgumentException;

abstract class BaseApi
{
    /**
     * Returns OK if request is successful.
     */
    const ERRORCODE_OK = 'OK';

    const TRANSACTIONERRORCODE_OPERATIONCANCELLEDBYCUSTOMER = 'OperationCancelledbyCustomer';

    const PURCHASEOPERATION_SALE = 'SALE';

    /**
     * If AUTHORIZATION is submitted, this indicates that the order will be a 2-phased transaction if the payment method supports it.
     */
    const PURCHASEOPERATION_AUTHORIZATION = 'AUTHORIZATION';

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
     * @param array             $options
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(SoapClientFactory $clientFactory, array $options)
    {
        $this->clientFactory = $clientFactory;
        $this->options = $options;

        if (true == empty($this->options['account_number'])) {
            throw new InvalidArgumentException('The account_number option must be set.');
        }

        if (true == empty($this->options['encryption_key'])) {
            throw new InvalidArgumentException('The encryption_key option must be set.');
        }

        if (false == is_bool($this->options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
    }

    /**
     * @param string $operation
     * @param array  $parameters
     * @param string $serviceWsdl
     *
     * @return array
     */
    protected function call($operation, array $parameters, $serviceWsdl)
    {
        $client = $this->clientFactory->createWsdlClient($serviceWsdl);

        $response = @$client->$operation($parameters);

        $result = $this->convertSimpleXmlToArray(new \SimpleXMLElement($response->{$operation.'Result'}));

        $result = $this->normalizeStatusFields($result);
        $result = $this->removeHeader($result);
        $result = $this->removeObsolete($result);

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

        return md5(trim(implode("", $orderedParameters)).$this->options['encryption_key']);
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
        if (array_key_exists('status', $inputResult) && is_array($inputResult['status'])) {
            $statuses = $inputResult['status'];

            //agreement.autoPay seems has a bug. it returns two sub arrays inside status. Lets take the first as status.
            if (is_array(current($statuses))) {
                $statuses = array_shift($statuses);
            }

            foreach ($statuses as $name => $value) {
                if ('description' == $name) {
                    $name = 'errorDescription';
                }

                $result[$name] = $value;
            }
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

    /**
     * @param  array $inputResult
     * @return array
     */
    protected function removeObsolete(array $inputResult)
    {
        $result = $inputResult;

        unset($result['code']);
        unset($result['sessionRef']);

        return $result;
    }
}
