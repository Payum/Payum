<?php

namespace Payum\Core\Bridge\Httplug;

use Payum\Core\HttpClientInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s is deprecated and will be removed in 3.0. Use a %s — the one Http\Discovery\Psr18ClientDiscovery::find() returns, or your own — instead.', HttplugClient::class, ClientInterface::class);

/**
 * This is a HttpClient that support Httplug. This is an adapter class that make sure we can use Httplug without breaking
 * backward compatibility.
 *
 * @deprecated since 2.0.0, will be removed in 3.0. Use a Psr\Http\Client\ClientInterface — the one
 *             Http\Discovery\Psr18ClientDiscovery::find() returns, or your own — instead.
 */
class HttplugClient implements HttpClientInterface, ClientInterface
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @deprecated since 2.0.0, will be removed in 3.0. Use
     *             Psr\Http\Client\ClientInterface::sendRequest() instead.
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        trigger_deprecation('payum/core', '2.0.0', 'The %s::send() is deprecated and will be removed in 3.0. Use %s::sendRequest() instead.', self::class, ClientInterface::class);

        return $this->sendRequest($request);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }
}
