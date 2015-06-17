<?php

namespace Payum\Core\Bridge\Guzzle;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Payum\Core\Bridge\Psr\Http\ClientInterface;



class ClientTest implements ClientInterface
{
    protected $client;

    /**
     * @param GuzzleClientInterface $client
     */
    public function  __construct(GuzzleClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function send(RequestInterface $request)
    {
        return $this->client->send($request);
    }
}