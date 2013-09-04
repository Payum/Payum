<?php
namespace Payum\Bundle\PayumBundle\Security;

use Payum\Exception\InvalidArgumentException;
use Payum\Model\TokenizedDetails;
use Payum\Security\HttpRequestVerifierInterface;
use Payum\Security\Token;
use Payum\Security\TokenInterface;
use Payum\Storage\StorageInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

class HttpRequestVerifier implements HttpRequestVerifierInterface
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
        if ($httpRequest instanceof Request) {
            throw new InvalidArgumentException('Invalid request given.');
        }

        if (false === $token = $httpRequest->attributes->get('payum_token', $httpRequest->get('payum_token', false))) {
            throw new NotFoundHttpException('Token parameter not set in request');
        }

        $isSubRequest = true;
        if (false == $token instanceof Token) {
            $isSubRequest = false;
            if (false == $token = $this->tokenStorage->findModelById($token)) {
                throw new NotFoundHttpException(sprintf('The token model with hash %s not found.', $token));
            }
        }

        /** @var $token Token */

        if (false === $isSubRequest && parse_url($httpRequest->getUri(), PHP_URL_PATH) != parse_url($token->getTargetUrl(), PHP_URL_PATH)) {
            throw new HttpException(400, sprintf('The current url %s not match target url %s set in the token.', $httpRequest->getRequestUri(), $token->getTargetUrl()));
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