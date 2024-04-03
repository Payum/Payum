<?php

namespace Payum\Core\Bridge\PlainPhp\Security;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\Util\RequestTokenVerifier;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use function array_merge;

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

    public function verify($httpRequest): TokenInterface
    {
        if ($httpRequest instanceof Request) {
            $httpRequest = array_merge($httpRequest->request->all(), $httpRequest->query->all());
        }

        if (! is_array($httpRequest)) {
            throw new InvalidArgumentException('Invalid request given. In most cases you have to pass $_REQUEST array.');
        }

        if (! isset($httpRequest[$this->tokenParameter])) {
            throw new InvalidArgumentException(sprintf('Token parameter `%s` was not found in in the http request.', $this->tokenParameter));
        }

        if ($httpRequest[$this->tokenParameter] instanceof TokenInterface) {
            return $httpRequest[$this->tokenParameter];
        }

        if (! $token = $this->tokenStorage->find($httpRequest[$this->tokenParameter])) {
            throw new InvalidArgumentException(sprintf('A token with hash `%s` could not be found.', $httpRequest[$this->tokenParameter]));
        }

        /** @var TokenInterface $token */
        if (! RequestTokenVerifier::isValid($_SERVER['REQUEST_URI'], $token->getTargetUrl())) {
            throw new InvalidArgumentException(sprintf('The current url %s not match target url %s set in the token.', $_SERVER['REQUEST_URI'], $token->getTargetUrl()));
        }

        return $token;
    }

    public function invalidate(TokenInterface $token): void
    {
        $this->tokenStorage->delete($token);
    }
}
