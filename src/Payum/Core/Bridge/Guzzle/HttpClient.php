<?php
namespace Payum\Core\Bridge\Guzzle;

use GuzzleHttp\ClientInterface;
use Payum\Core\HttpClientInterface;
use Psr\Http\Message\RequestInterface;

class HttpClient implements HttpClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function send(RequestInterface $request)
    {
        return $this->client->send($request);
    }
}