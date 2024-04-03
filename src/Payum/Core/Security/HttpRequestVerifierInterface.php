<?php

namespace Payum\Core\Security;

use Exception;
use InvalidArgumentException;

interface HttpRequestVerifierInterface
{
    /**
     * @param mixed $httpRequest It is up to implementation decide what is request.
     *
     * @throws InvalidArgumentException if request not supported
     * @throws Exception if token verification failed.
     *
     * @return TokenInterface
     */
    public function verify($httpRequest);

    /**
     * This method invalidate token so it could not be used in future.
     */
    public function invalidate(TokenInterface $token);
}
