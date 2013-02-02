<?php
namespace Payum\Be2Bill;

use Buzz\Client\ClientInterface;
use Buzz\Message\Form\FormRequest;

use Payum\Exception\InvalidArgumentException;
use Payum\Exception\Http\HttpResponseStatusNotSuccessfulException;
use Payum\Bridge\Buzz\JsonResponse;

class Api
{
    const VERSION = '2.0';
    
    const EXECCODE_SUCCESSFUL = '0000';

    /**
     * @var \Buzz\Client\ClientInterface
     */
    protected $client;

    /**
     * @var array
     */
    protected $options = array(
        'identifier' => null,
        'password' => null,
        'sandbox' => null,
    );

    /**
     * @param \Buzz\Client\ClientInterface $client
     * @param array $options
     * 
     * @throws InvalidArgumentException if an option is invalid
     */
    public function __construct(ClientInterface $client, array $options)
    {
        $this->client = $client;
        $this->options = array_replace($this->options, $options);

        if (true == empty($this->options['identifier'])) {
            throw new InvalidArgumentException('The identifier option must be set.');
        }
        if (true == empty($this->options['password'])) {
            throw new InvalidArgumentException('The password option must be set.');
        }
        if (false == is_bool($this->options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
    }

    /**
     * @param array $params
     * 
     * @return \Payum\Bridge\Buzz\JsonResponse
     */
    public function payment(array $params)
    {
        $request = new FormRequest();

        $params['VERSION'] = self::VERSION;
        $params['IDENTIFIER'] = $this->options['identifier'];
        $params['OPERATIONTYPE'] = 'payment';
        $params['HASH'] = $this->calculateHash($params);
        
        $request->setField('method', 'payment');
        $request->setField('params', $params);
        
        return $this->doRequest($request);
    }

    /**
     * @param \Buzz\Message\Form\FormRequest $request
     *
     * @throws \Payum\Exception\Http\HttpResponseStatusNotSuccessfulException
     *
     * @return JsonResponse
     */
    protected function doRequest(FormRequest $request)
    {
        $request->setMethod('POST');
        $request->fromUrl($this->getApiEndpoint());

        $this->client->send($request, $response = new JsonResponse());

        if (false == $response->isSuccessful()) {
            throw new HttpResponseStatusNotSuccessfulException($request, $response);
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://secure-test.be2bill.com/front/service/rest/process' :
            'https://secure-magenta1.be2bill.com/front/service/rest/process'
        ;
    }
    
    /**
     * @param array $fields
     * 
     * @return string
     */
    protected function calculateHash(array $params)
    {
        #Alpha sort
        ksort($params);
        
        $clearString = $this->options['password'];
        foreach ($params as $key => $value) {
            $clearString .= $key . '=' . $value . $this->options['password'];
        }

        return hash('sha256', $clearString);
    }
    
}