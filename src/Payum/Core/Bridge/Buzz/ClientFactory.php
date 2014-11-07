<?php
namespace Payum\Core\Bridge\Buzz;

use Buzz\Client\Curl;

class ClientFactory
{
    /**
     * @return Curl
     */
    public static function createCurl()
    {
        $client = new Curl();

        //reaction to the ssl3.0 shutdown from paypal
        //https://www.paypal-community.com/t5/PayPal-Forward/PayPal-Response-to-SSL-3-0-Vulnerability-aka-POODLE/ba-p/891829
        //http://googleonlinesecurity.blogspot.com/2014/10/this-poodle-bites-exploiting-ssl-30.html
        $client->setOption(CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
        $client->setOption(CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);

        return $client;
    }
}