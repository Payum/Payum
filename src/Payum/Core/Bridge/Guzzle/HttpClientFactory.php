<?php
namespace Payum\Core\Bridge\Guzzle;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Payum\Core\HttpClientInterface;

class HttpClientFactory
{
    /**
     * @return GuzzleClientInterface
     */
    public static function createGuzzle()
    {
        // Reaction to the ssl3.0 shutdown from paypal
        // https://www.paypal-community.com/t5/PayPal-Forward/PayPal-Response-to-SSL-3-0-Vulnerability-aka-POODLE/ba-p/891829
        // http://googleonlinesecurity.blogspot.com/2014/10/this-poodle-bites-exploiting-ssl-30.html

        $curlOptions = [
            //There is a constant for that CURL_SSLVERSION_TLSv1, but it is not present on some versions of php.
            CURLOPT_SSLVERSION => 1,
        ];

        // Do not use the Cipher List for NSS
        // https://github.com/paypal/sdk-core-php/blob/namespace-5.3/lib/PayPal/Core/PPHttpConfig.php#L51
        $curl = curl_version();
        $sslVersion = isset($curl['ssl_version']) ? $curl['ssl_version'] : '';
        if (false === strpos($sslVersion, "NSS/")) {
            $curlOptions[CURLOPT_SSL_CIPHER_LIST] = 'TLSv1';
        }

        return new GuzzleClient([
            'curl' => $curlOptions
        ]);
    }

    /**
     * @return HttpClientInterface
     */
    public static function create()
    {
        return new HttpClient(static::createGuzzle());
    }
}
