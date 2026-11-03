<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Builder\HttpRequestVerifierBuilder from payum/payum-bundle instead.', HttpRequestVerifierBuilder::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Builder\HttpRequestVerifierBuilder from payum/payum-bundle instead.
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
