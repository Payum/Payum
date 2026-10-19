<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\HttpClientInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A 1.x {@see HttpClientInterface} set as `payum.http_client`, handed to gateways that now take a PSR-18
 * client while still answering the 1.x gateways that call send().
 *
 * @deprecated since 2.0, removed in 3.0. Set a {@see ClientInterface} as `payum.http_client` instead.
 */
final class Psr18HttpClient implements ClientInterface, HttpClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->send($request);
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        return $this->client->send($request);
    }
}
