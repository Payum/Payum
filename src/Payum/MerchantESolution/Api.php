<?php
namespace Payum\MerchantESolution;

use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\HttpClientInterface;
use Payum\Core\Exception\Http\HttpException;
use Guzzle\Http\Client;

class Api
{
    const OPERATION_PAYMENT = 'payment';
    var $ISOCurrencyCode = array("USD" => "840", "CAD" => "124", "GBP" => "826", "EUR" => "978");

    protected $options = array(
        'profileId' => null,
        'profileKey' => null,
    );

    public function __construct(array $options, HttpClientInterface $client = null)
    {
        //error_log(print_R($options, TRUE), 3, "error.log");
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty(array(
            'profile_id',
            'profile_key',

        ));

        $this->options = $options;
        $this->client = $client ?: HttpClientFactory::create();
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function payment(array $params)
    {
        $params['OPERATIONTYPE'] = static::OPERATION_PAYMENT;

        return $this->doRequest(array(
            'method' => 'payment',
            'params' => $params
        ));
    }

    function doPayment(array $fields)
    {
        $fields = $this->addAuthorizeFields($fields);
        $result = $this->doRequest($fields);
        return $result;
    }

    function getApiEndpoint()
    {
        return 'https://cert.merchante-solutions.com/mes-api/tridentApi';
    }

    function addAuthorizeFields(array $fields)
    {
        $fields['profile_id'] = $this->options['profile_id'];
        $fields['profile_key'] = $this->options['profile_key'];
        return $fields;
    }

    function doRequest(array $fields)
    {
        $fields = $this->addAuthorizeFields($fields);
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );
        $client = new Client($this->getApiEndpoint());
        $request = $client->createRequest('POST', $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = array();
        parse_str($response->getBody(), $result);
        foreach ($result as &$value) {
            $value = urldecode($value);
        }
        return $result;
    }

    public function status()
    {
        return count($this->errors);
    }
}