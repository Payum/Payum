<?php
namespace Payum\Core\Bridge\Guzzle;

use GuzzleHttp\ClientInterface;
use Payum\Core\HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This is a HttpClient that is using Guzzle.
 *
 * @deprecated This will be removed in 2.0. Consider using Http\Client\HttpClient.
 */
class HttpClient implements HttpClientInterface
{
    public function __construct(private ClientInterface $client)
    {}

    /**
     * {@inheritDoc}
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        return $this->client->send($request);
    }
}
