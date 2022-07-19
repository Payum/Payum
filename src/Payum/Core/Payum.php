<?php

namespace Payum\Core;

use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;

/**
 * @template StorageType of object
 * @implements RegistryInterface<StorageType>
 */
class Payum implements RegistryInterface
{
    /**
     * @var RegistryInterface<StorageType>
     */
    protected RegistryInterface $registry;

    protected HttpRequestVerifierInterface $httpRequestVerifier;

    protected GenericTokenFactoryInterface $tokenFactory;

    /**
     * @var StorageInterface<TokenInterface>
     */
    protected StorageInterface $tokenStorage;

    /**
     * @param RegistryInterface<StorageType> $registry
     * @param StorageInterface<TokenInterface> $tokenStorage
     */
    public function __construct(
        RegistryInterface $registry,
        HttpRequestVerifierInterface $httpRequestVerifier,
        GenericTokenFactoryInterface $tokenFactory,
        StorageInterface $tokenStorage
    ) {
        $this->registry = $registry;
        $this->httpRequestVerifier = $httpRequestVerifier;
        $this->tokenFactory = $tokenFactory;
        $this->tokenStorage = $tokenStorage;
    }

    public function getGatewayFactory(string $name): GatewayFactoryInterface
    {
        return $this->registry->getGatewayFactory($name);
    }

    /**
     * @return GatewayFactoryInterface[]
     */
    public function getGatewayFactories(): array
    {
        return $this->registry->getGatewayFactories();
    }

    public function getGateway(string $name): GatewayInterface
    {
        return $this->registry->getGateway($name);
    }

    /**
     * @return GatewayInterface[]
     */
    public function getGateways(): array
    {
        return $this->registry->getGateways();
    }

    /**
     * @param class-string<StorageType> $class
     * @return StorageInterface<StorageType>
     */
    public function getStorage($class): StorageInterface
    {
        return $this->registry->getStorage($class);
    }

    /**
     * @return array<class-string, StorageInterface<StorageType>>
     */
    public function getStorages(): array
    {
        return $this->registry->getStorages();
    }

    public function getHttpRequestVerifier(): HttpRequestVerifierInterface
    {
        return $this->httpRequestVerifier;
    }

    public function getTokenFactory(): GenericTokenFactoryInterface
    {
        return $this->tokenFactory;
    }

    /**
     * @return StorageInterface<TokenInterface>
     */
    public function getTokenStorage(): StorageInterface
    {
        return $this->tokenStorage;
    }
}
