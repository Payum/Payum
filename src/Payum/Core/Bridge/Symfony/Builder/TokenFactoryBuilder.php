<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

@trigger_error('The '.__NAMESPACE__.'\TokenFactoryBuilder class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
class TokenFactoryBuilder
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(): TokenFactoryInterface
    {
        return $this->build(...func_get_args());
    }

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     * @param StorageRegistryInterface<StorageInterface<TokenInterface>> $storageRegistry
     */
    public function build(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry): TokenFactoryInterface
    {
        return new TokenFactory($tokenStorage, $storageRegistry, $this->urlGenerator);
    }
}
