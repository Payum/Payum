<?php

declare(strict_types=1);

namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Psr\Http\Message\ServerRequestInterface;
use function array_merge;
use function is_array;

/**
 * Answers the 1.x GetHttpRequest from the PSR-7 request the container holds.
 *
 * The flat arrays are kept only here, so that a gateway written against 1.x needs no change. PSR-7 is
 * the 2.0 contract: a handler reads $context->httpRequest() instead.
 */
class GetHttpRequestAction implements ActionInterface
{
    public function __construct(
        private readonly ServerRequestInterface $httpRequest
    ) {
    }

    /**
     * @param GetHttpRequest $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $parsedBody = $this->httpRequest->getParsedBody();
        $serverParams = $this->httpRequest->getServerParams();

        $request->method = $this->httpRequest->getMethod();
        $request->uri = (string) $this->httpRequest->getUri();
        $request->query = $this->httpRequest->getQueryParams();

        // $_REQUEST, not $_POST: that is what 1.x promised, so a gateway reading ->request for a
        // parameter the PSP put on the query string still finds it.
        $request->request = array_merge($request->query, is_array($parsedBody) ? $parsedBody : []);

        $request->headers = $this->httpRequest->getHeaders();
        $request->clientIp = (string) ($serverParams['REMOTE_ADDR'] ?? '');
        $request->userAgent = $this->httpRequest->getHeaderLine('User-Agent');
        $request->content = (string) $this->httpRequest->getBody();
    }

    public function supports($request): bool
    {
        return $request instanceof GetHttpRequest;
    }
}
