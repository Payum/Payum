<?php

declare(strict_types=1);

namespace Payum\Skeleton;

use Payum\Core\Exception\Http\HttpException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function http_build_query;

final class SkeletonApi
{
    public function __construct(
        private readonly SkeletonConfig $config,
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $fields
     * @throws ClientExceptionInterface
     */
    public function send(string $method, string $path, array $fields): ResponseInterface
    {
        $request = $this->requestFactory
            ->createRequest($method, $this->config->baseUrl() . $path)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(http_build_query($fields)))
        ;

        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }
}
