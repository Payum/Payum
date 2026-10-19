<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

use LogicException;
use Stripe\HttpClient\ClientInterface;
use function array_shift;
use function json_encode;
use function sprintf;

/**
 * The Stripe SDK talks to Stripe with its own client rather than `payum.http_client`.
 */
final class StripeMockClient implements ClientInterface
{
    /**
     * @var list<array{method: string, url: string, params: mixed}>
     */
    public array $sent = [];

    /**
     * @param list<array{0: int, 1: array<string, mixed>}> $responses
     */
    public function __construct(
        private array $responses,
    ) {
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $params
     *
     * @return array{0: string, 1: int, 2: array<string, string>}
     */
    public function request($method, $absUrl, $headers, $params, $hasFile)
    {
        $this->sent[] = [
            'method' => $method,
            'url' => $absUrl,
            'params' => Recorder::plain($params),
        ];

        $response = array_shift($this->responses);

        if (null === $response) {
            throw new LogicException(sprintf('No Stripe response queued for %s %s.', $method, $absUrl));
        }

        return [json_encode($response[1], JSON_THROW_ON_ERROR), $response[0], []];
    }
}
