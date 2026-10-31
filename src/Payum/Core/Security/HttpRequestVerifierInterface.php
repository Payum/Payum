<?php

namespace Payum\Core\Security;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

interface HttpRequestVerifierInterface
{
    /**
     * Every implementation must accept a ServerRequestInterface, and check the token against its URI rather
     * than against $_SERVER. Other request shapes some implementations accept are removed in 3.0.
     *
     * @param ServerRequestInterface|mixed $httpRequest
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
