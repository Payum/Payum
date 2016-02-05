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
        $curl = curl_version();

        $curlOptions = [
            CURLOPT_USERAGENT => sprintf('Payum/1.x curl/%s PHP/%s', $curl['version'], phpversion()),
        ];

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
