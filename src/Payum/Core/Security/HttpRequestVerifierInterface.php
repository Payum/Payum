<?php
namespace Payum\Core\Security;

interface HttpRequestVerifierInterface
{
    /**
     * @param mixed $httpRequest It is up to implementation decide what is request.
     *
     * @throws \InvalidArgumentException if request not supported
     * @throws \Exception                if token verification failed.
     */
    public function verify(mixed $httpRequest): TokenInterface;

    /**
     * This method invalidate token so it could not be used in future.
     */
    public function invalidate(TokenInterface $token): void;
}
