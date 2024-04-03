<?php

namespace Payum\Skeleton;

use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function http_build_query;

class Api
{
    protected ClientInterface $client;

    protected RequestFactoryInterface $requestFactory;

    protected StreamFactoryInterface $streamFactory;

    /**
     * @var array<string, mixed>
     */
    protected array $options = [];

    /**
     * @param array<string, mixed> $options
     * @throws InvalidArgumentException if an option is invalid
     */
    public function __construct(
        array $options,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ) {
        $this->options = $options;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param array<string, mixed> $fields
     * @throws ClientExceptionInterface
     */
    protected function doRequest(string $method, array $fields): ResponseInterface
    {
        $request = $this->requestFactory
            ->createRequest($method, $this->getApiEndpoint())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(http_build_query($fields)))
        ;

        $response = $this->client->sendRequest($request);

        if (! ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    protected function getApiEndpoint(): string
    {
        return $this->options['sandbox'] ? 'https://sandbox.example.com' : 'https://example.com';
    }
}
