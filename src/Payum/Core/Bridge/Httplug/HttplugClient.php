<?php
namespace Payum\Core\Bridge\Httplug;

use Payum\Core\HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;

/**
 * This is a HttpClient that support Httplug. This is an adapter class that make sure we can use Httplug without breaking
 * backward compatibility. At 2.0 we will be using Http\Client\HttpClient.
 *
 * @deprecated This will be removed in 2.0. Consider using Http\Client\HttpClient.
 */
class HttplugClient implements HttpClientInterface
{
    public function __construct(private HttpClient $client)
    {}

    /**
     * {@inheritDoc}
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }
}
