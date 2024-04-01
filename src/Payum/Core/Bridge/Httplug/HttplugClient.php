<?php

namespace Payum\Core\Bridge\Httplug;

use Http\Client\HttpClient;
use Payum\Core\HttpClientInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function trigger_error;

trigger_error('The ' . __NAMESPACE__ . '\HttplugClient is deprecated since 2.0.0 and will be removed in 3.0. Use Psr18ClientDiscovery::find() instead.', E_USER_DEPRECATED);

/**
 * This is a HttpClient that support Httplug. This is an adapter class that make sure we can use Httplug without breaking
 * backward compatibility. At 2.0 we will be using Http\Client\HttpClient.
 *
 * @deprecated This will be removed in 2.0. Consider using Http\Client\HttpClient.
 */
class HttplugClient implements HttpClientInterface, ClientInterface
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @deprecated since 2.0.0, will be removed in 3.0. Use sendRequest() instead.
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        trigger_error('The ' . __CLASS__ . '::send() is deprecated since 2.0.0 and will be removed in 3.0. Use sendRequest() instead.', E_USER_DEPRECATED);
        return $this->sendRequest($request);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }
}
