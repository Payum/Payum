<?php
namespace Payum\Security;

use Payum\Exception\InvalidArgumentException;
use Payum\Storage\StorageInterface;

class PlainHttpRequestVerifier implements HttpRequestVerifierInterface
{
    /**
     * @var \Payum\Storage\StorageInterface
     */
    protected $tokenStorage;

    /**
     * @param StorageInterface $tokenStorage
     */
    public function __construct(StorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function verify($httpRequest)
    {
        if (false == is_array($httpRequest)) {
            throw new InvalidArgumentException('Invalid request given. In most cases you have to pass $_REQUEST array.');
        }

        $tokenParameter = 'payum_token';
        if (false == isset($httpRequest[$tokenParameter])) {
            throw new InvalidArgumentException(sprintf('Token parameter `%s` not set in request.', $tokenParameter));
        }

        if (false == $token = $this->tokenStorage->findModelById($httpRequest[$tokenParameter])) {
            throw new InvalidArgumentException(sprintf('A token with hash `%s` could not be found.', $httpRequest[$tokenParameter]));
        }

        /** @var $token Token */

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
        $this->tokenStorage->deleteModel($token);
    }
}