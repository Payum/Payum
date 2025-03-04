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

@trigger_error('The ' . __NAMESPACE__ . '\HttpRequestVerifier class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
class HttpRequestVerifier implements HttpRequestVerifierInterface
{
    /**
     * @var StorageInterface<TokenInterface>
     */
    protected StorageInterface $tokenStorage;

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     */
    public function __construct(StorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function verify($httpRequest)
    {
        if (! $httpRequest instanceof Request) {
            throw new InvalidArgumentException(sprintf(
                'Invalid request given. Expected %s but it is %s',
                Request::class,
                get_debug_type($httpRequest)
            ));
        }

        if (! $hash = $httpRequest->attributes->get('payum_token', $httpRequest->get('payum_token', false))) {
            throw new NotFoundHttpException('Token parameter not set in request');
        }

        if ($hash instanceof TokenInterface) {
            $token = $hash;
        } else {
            if (! $token = $this->tokenStorage->find($hash)) {
                throw new NotFoundHttpException(sprintf('A token with hash `%s` could not be found.', $hash));
            }

            if (! RequestTokenVerifier::isValid($httpRequest->getUri(), $token->getTargetUrl())) {
                throw new HttpException(400, sprintf('The current url %s not match target url %s set in the token.', $httpRequest->getUri(), $token->getTargetUrl()));
            }
        }

        return $token;
    }

    public function invalidate(TokenInterface $token): void
    {
        $this->tokenStorage->delete($token);
    }
}
