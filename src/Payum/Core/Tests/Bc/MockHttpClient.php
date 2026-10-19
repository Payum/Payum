<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

use LogicException;
use Nyholm\Psr7\Response;
use Payum\Core\HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use function sprintf;
use function str_contains;

/**
 * Answers `payum.http_client` and keeps what was sent, so a gateway that starts making a call 1.x did not
 * make, or stops making one, shows up in the transcript.
 *
 * Each response is answered once, to the first request whose body contains its third element, or to any
 * request when it has none.
 */
final class MockHttpClient implements HttpClientInterface
{
    /**
     * @var list<array{method: string, uri: string, body: string}>
     */
    public array $sent = [];

    /**
     * @param list<array{0: int, 1: string, 2?: string}> $responses
     */
    public function __construct(
        private array $responses,
    ) {
    }

    public function send(RequestInterface $request)
    {
        $body = (string) $request->getBody();

        $this->sent[] = [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'body' => $body,
        ];

        foreach ($this->responses as $i => $response) {
            if (! isset($response[2]) || str_contains($body, $response[2])) {
                unset($this->responses[$i]);

                return new Response($response[0], [], $response[1]);
            }
        }

        throw new LogicException(sprintf('No response queued for %s %s.', $request->getMethod(), $request->getUri()));
    }
}
