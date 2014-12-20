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

        //There is a constant for that CURL_SSLVERSION_TLSv1, but it is not present on some versions of php.
        $client->setOption(CURLOPT_SSLVERSION, $curlSslVersionTlsV1 = 1);

        return $client;
    }
}
