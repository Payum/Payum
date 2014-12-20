<?php
namespace Payum\Core\Bridge\Symfony\Security;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

class HttpRequestVerifier implements HttpRequestVerifierInterface
{
    /**
     * @var \Payum\Core\Storage\StorageInterface
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
        if (false == $httpRequest instanceof Request) {
            throw new InvalidArgumentException(sprintf(
                'Invalid request given. Expected %s but it is %s',
                'Symfony\Component\HttpFoundation\Request',
                is_object($httpRequest) ? get_class($httpRequest) : gettype($httpRequest)
            ));
        }

        if (false === $hash = $httpRequest->attributes->get('payum_token', $httpRequest->get('payum_token', false))) {
            throw new NotFoundHttpException('Token parameter not set in request');
        }

        if ($hash instanceof TokenInterface) {
            $token = $hash;
        } else {
            if (false == $token = $this->tokenStorage->find($hash)) {
                throw new NotFoundHttpException(sprintf('A token with hash `%s` could not be found.', $hash));
            }

            if (parse_url($httpRequest->getUri(), PHP_URL_PATH) != parse_url($token->getTargetUrl(), PHP_URL_PATH)) {
                throw new HttpException(400, sprintf('The current url %s not match target url %s set in the token.', $httpRequest->getUri(), $token->getTargetUrl()));
            }
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
