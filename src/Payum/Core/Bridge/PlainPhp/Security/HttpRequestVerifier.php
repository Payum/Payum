<?php

namespace Payum\Core\Bridge\PlainPhp\Security;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\Util\RequestTokenVerifier;
use Payum\Core\Storage\StorageInterface;

class HttpRequestVerifier implements HttpRequestVerifierInterface
{
    /**
     * @var StorageInterface
     */
    protected $tokenStorage;

    /**
     * @var string
     */
    protected $tokenParameter;

    /**
     * @param string           $tokenParameter
     */
    public function __construct(StorageInterface $tokenStorage, $tokenParameter = 'payum_token')
    {
        $this->tokenStorage = $tokenStorage;
        $this->tokenParameter = (string) $tokenParameter;
    }

    public function verify($httpRequest)
    {
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
