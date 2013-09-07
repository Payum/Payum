<?php
namespace Payum\Security;

interface HttpRequestVerifierInterface
{
    /**
     * @param mixed $httpRequest It is up to implementation decide what is request.
     *
     * @throws \InvalidArgumentException if request not supported
     * @throws \Exception if token verification failed.
     *
     * @return TokenInterface
     */
    function verify($httpRequest);

    /**
     * This method invalidate token so it could not be used in future.
     *
     * @param TokenInterface $token
     *
     * @return void
     */
    function invalidate(TokenInterface $token);
}