<?php

namespace Payum\Core\Bridge\Symfony\Security;

use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TokenFactory extends AbstractTokenFactory
{
    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

        parent::__construct($tokenStorage, $storageRegistry);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function generateUrl(string $path, array $parameters = []): string
    {
        return $this->urlGenerator->generate($path, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
