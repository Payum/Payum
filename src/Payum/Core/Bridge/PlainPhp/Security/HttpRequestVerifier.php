<?php
namespace Payum\Core\Bridge\PlainPhp\Security;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;

class HttpRequestVerifier implements HttpRequestVerifierInterface
{
    /**
     * @var \Payum\Core\Storage\StorageInterface
     */
    protected $tokenStorage;

    /**
     * @var string
     */
    protected $tokenParameter;

    /**
     * @param StorageInterface $tokenStorage
     * @param string           $tokenParameter
     */
    public function __construct(StorageInterface $tokenStorage, $tokenParameter = 'payum_token')
    {
        $this->tokenStorage = $tokenStorage;
        $this->tokenParameter = (string) $tokenParameter;
    }

    /**
     * {@inheritDoc}
     */
    public function verify($httpRequest)
    {
        if (false == is_array($httpRequest)) {
            throw new InvalidArgumentException('Invalid request given. In most cases you have to pass $_REQUEST array.');
        }

        if (false == isset($httpRequest[$this->tokenParameter])) {
            throw new InvalidArgumentException(sprintf('Token parameter `%s` was not found in in the http request.', $this->tokenParameter));
        }

        if ($httpRequest[$this->tokenParameter] instanceof TokenInterface) {
            return $httpRequest[$this->tokenParameter];
        }

        if (false == $token = $this->tokenStorage->find($httpRequest[$this->tokenParameter])) {
            throw new InvalidArgumentException(sprintf('A token with hash `%s` could not be found.', $httpRequest[$this->tokenParameter]));
        }

        /** @var $token TokenInterface */

        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != parse_url($token->getTargetUrl(), PHP_URL_PATH)) {
            throw new InvalidArgumentException(sprintf('The current url %s not match target url %s set in the token.', $_SERVER['REQUEST_URI'], $token->getTargetUrl()));
        }

        return $token;
    }

    /**
     * {@inheritDoc}
     */
    public function invalidate(TokenInterface $token)
    {
        $this->tokenStorage->delete($token);
    }
}
