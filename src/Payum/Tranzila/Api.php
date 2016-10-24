<?php
namespace Payum\Tranzila;

use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\HttpClientInterface;
use Guzzle\Http\Client;
use Payum\Core\Exception\Http\HttpException;

class Api
{
    const OPERATION_PAYMENT = 'payment';
    protected $options = array(
        'seller_payme_id' => null,
        'test_mode' => null,
    );

    public function __construct(array $options, HttpClientInterface $client = null)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty(array(
            'seller_payme_id',
            'test_mode',
        ));

        $this->options = $options;
        $this->client = $client ?: HttpClientFactory::create();
    }

    /**
     * @param array $params
     *
     * @return array
     */

    function doPayment(array $fields)
    {
        $fields = $this->addAuthorizeFields($fields);
        $result = $this->doRequest($fields);
        return $result;
    }

    protected function doRequest(array $fields)
    {
        if ($fields['refund'] == 'false') {
            $saleResponse = $this->generateSaleID($fields);
        } else {
            $saleResponse = json_encode(array('status_code' => 0));
        }
        $response = $this->makePayment($saleResponse, $fields);
        return $response;
    }

    function getApiEndpoint()
    {
        if ($this->options['test_mode'] == 1) {
            $url = 'https://preprod.paymeservice.com/api/generate-sale';
        } else {
            $url = 'https://ng.paymeservice.com/api/generate-sale';
        }
        return $url;
    }

    function getApiUrl($param)
    {
        if ($this->options['test_mode'] == 1) {
            if ($param['refund'] == 'false')
                $url = 'https://preprod.paymeservice.com/api/pay-sale';
            else
                $url = 'https://preprod.paymeservice.com/api/refund-sale';
        } else {
            if ($param['refund'] == 'false')
                $url = 'https://ng.paymeservice.com/api/pay-sale';
            else
                $url = 'https://ng.paymeservice.com/api/refund-sale';
        }
        return $url;
    }

    private function generateSaleID(array $data)
    {
      $data = $this->addAuthorizeFields($data);
      $data_string = json_encode($data);
        $headers = array(
            'content-type' => 'application/json',
        );

        $client = new Client($this->getApiEndpoint());

        $request = $client->post($this->getApiEndpoint(),$headers,array());
        $request->setBody($data_string); #set body!
        $response = $request->send();


        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = array();
        parse_str($response->getBody(),$result);

        foreach ($result as $key=>$value) {
           $result = urldecode(stripslashes($key));
        }
        return $result;
    }

    private function makePayment($data, $param)
    {
        $data = json_decode($data, true);

        if ($data['status_code'] == 0) {
            if ($param['refund'] == 'false') {
                $param = $this->addAuthorizeFields($param);
                $param['payme_sale_id'] = $data['payme_sale_id'];
            } else {

                $transactionID = explode("~", $param['id']);
                $postvalues=  array(
                'seller_payme_id' => $this->options['seller_payme_id'],
                    "payme_sale_id" => $transactionID[0],
                    "language" => "en",
            );
            }
            if($param['refund'] == 'false'){
                $data_string = json_encode($param);
            }else {
                $data_string = json_encode($postvalues);
            }
            $headers = array(
                'content-type' => 'application/json',
                'Content-Length: ' . strlen($data_string),
            );
            $client = new Client($this->getApiEndpoint());
            $request = $client->post($this->getApiUrl($param), $headers ,array());
            $request->setBody($data_string); #set body!
            $response = $request->send();

            if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
                throw HttpException::factory($request, $response);
            }

            $result = array();
            parse_str($response->getBody(), $result);
            foreach ($result as $key=>$value) {
                $result = urldecode(stripslashes($key));
            }
            $result = json_decode($result, true);
            return $result;
        } else {
            return $data;
        }
    }

    function addAuthorizeFields(array $fields){
        $fields['seller_payme_id'] = $this->options['seller_payme_id'];
        $fields['installments'] = 1;
        $fields['language']='en';
        return $fields;
    }

}
