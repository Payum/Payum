<?php

namespace Payum\Core\Bridge\Symfony\Security;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\Util\RequestTokenVerifier;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HttpRequestVerifier implements HttpRequestVerifierInterface
{
    /**
     * @var StorageInterface
     */
    protected $tokenStorage;

    public function __construct(StorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function verify($httpRequest)
    {
        if (false == $httpRequest instanceof Request) {
            throw new InvalidArgumentException(sprintf(
                'Invalid request given. Expected %s but it is %s',
                Request::class,
                is_object($httpRequest) ? $httpRequest::class : gettype($httpRequest)
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

            if (! RequestTokenVerifier::isValid($httpRequest->getUri(), $token->getTargetUrl())) {
                throw new HttpException(400, sprintf('The current url %s not match target url %s set in the token.', $httpRequest->getUri(), $token->getTargetUrl()));
            }
        }

        return $token;
    }

    public function invalidate(TokenInterface $token)
    {
        $this->tokenStorage->delete($token);
    }
}
