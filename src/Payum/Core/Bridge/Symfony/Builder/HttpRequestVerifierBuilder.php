<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;

@trigger_error('The '.__NAMESPACE__.'\HttpRequestVerifierBuilder class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
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
