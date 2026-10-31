<?php

declare(strict_types=1);

namespace Payum\Core\Security;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Security\Util\RequestTokenVerifier;
use Payum\Core\Storage\StorageInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpRequestVerifier implements HttpRequestVerifierInterface
{
    /**
     * @var StorageInterface<TokenInterface>
     */
    protected StorageInterface $tokenStorage;

    protected string $tokenParameter;

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     */
    public function __construct(StorageInterface $tokenStorage, string $tokenParameter = 'payum_token')
    {
        $this->tokenStorage = $tokenStorage;
        $this->tokenParameter = $tokenParameter;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function verify($httpRequest): TokenInterface
    {
        if (! $httpRequest instanceof ServerRequestInterface) {
            throw new InvalidArgumentException(sprintf('Invalid request given. Expected %s but it is %s.', ServerRequestInterface::class, get_debug_type($httpRequest)));
        }

        $body = $httpRequest->getParsedBody();

        return $this->verifyToken(
            $httpRequest->getAttribute($this->tokenParameter)
                ?? $httpRequest->getQueryParams()[$this->tokenParameter]
                ?? (is_array($body) ? $body[$this->tokenParameter] ?? null : null),
            (string) $httpRequest->getUri()
        );
    }

    public function invalidate(TokenInterface $token): void
    {
        $this->tokenStorage->delete($token);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function verifyToken(mixed $hashOrToken, string $requestUri): TokenInterface
    {
        if (null === $hashOrToken) {
            throw new InvalidArgumentException(sprintf('Token parameter `%s` was not found in in the http request.', $this->tokenParameter));
        }

        if ($hashOrToken instanceof TokenInterface) {
            return $hashOrToken;
        }

        $token = $this->tokenStorage->find($hashOrToken);

        if (! $token instanceof TokenInterface) {
            throw new InvalidArgumentException(sprintf('A token with hash `%s` could not be found.', $hashOrToken));
        }

        if (! RequestTokenVerifier::isValid($requestUri, $token->getTargetUrl())) {
            throw new InvalidArgumentException(sprintf('The current url %s not match target url %s set in the token.', $requestUri, $token->getTargetUrl()));
        }

        return $token;
    }
}
