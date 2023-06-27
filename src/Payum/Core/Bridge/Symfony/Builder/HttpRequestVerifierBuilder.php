<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;

class HttpRequestVerifierBuilder
{
    public function __invoke()
    {
        return call_user_func_array([$this, 'build'], func_get_args());
    }

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     */
    public function build(StorageInterface $tokenStorage): HttpRequestVerifier
    {
        return new HttpRequestVerifier($tokenStorage);
    }
}
