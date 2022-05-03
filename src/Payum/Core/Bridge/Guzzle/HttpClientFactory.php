<?php

namespace Payum\Core\Bridge\Guzzle;

use GuzzleHttp\Client;
use Http\Discovery\HttpClientDiscovery;
use Payum\Core\HttpClientInterface;

/**
 * @deprecated This will be removed in 2.0. Consider using Http\Discovery\HttpClientDiscovery.
 */
class HttpClientFactory
{
    /**
     * Create a Guzzle client.
     */
    public static function createGuzzle(): Client
    {
        $client = null;
        if (!class_exists(Client::class)) {
            @trigger_error('The function "HttpClientFactory::createGuzzle" is depcrecated and will be removed in 2.0.', E_USER_DEPRECATED);
            throw new \LogicException('Can not use "HttpClientFactory::createGuzzle" since Guzzle is not installed. This function is deprecated and will be removed in 2.0.');
        }

        $version = \GuzzleHttp\ClientInterface::VERSION;
        if ($version[0] !== '6') {
            throw new \LogicException('This version of Guzzle is not supported.');
        }

        $curl = curl_version();

        $curlOptions = [
             CURLOPT_USERAGENT => sprintf('Payum/1.x curl/%s PHP/%s', $curl['version'], PHP_VERSION),
         ];

        return new \GuzzleHttp\Client([
             'curl' => $curlOptions,
         ]);
    }

    public static function create(): HttpClientInterface
    {
        return new HttpClient(static::createGuzzle());
    }
}
