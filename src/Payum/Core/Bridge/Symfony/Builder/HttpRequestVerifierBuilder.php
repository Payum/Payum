<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;

class HttpRequestVerifierBuilder
{
    public function __invoke(): HttpRequestVerifierInterface
    {
        return $this->build(...func_get_args());
    }

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     */
    public function build(StorageInterface $tokenStorage): HttpRequestVerifierInterface
    {
        return new HttpRequestVerifier($tokenStorage);
    }
}
