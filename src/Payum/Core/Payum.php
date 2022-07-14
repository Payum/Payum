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
     * @param HttpRequestVerifierInterface $httpRequestVerifier
     * @param GenericTokenFactoryInterface $tokenFactory
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

    public function getGatewayFactory($name)
    {
        return $this->registry->getGatewayFactory($name);
    }

    public function getGatewayFactories()
    {
        return $this->registry->getGatewayFactories();
    }

    public function getGateway($name)
    {
        return $this->registry->getGateway($name);
    }

    public function getGateways()
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

    public function getStorages(): array
    {
        return $this->registry->getStorages();
    }

    /**
     * @return HttpRequestVerifierInterface
     */
    public function getHttpRequestVerifier()
    {
        return $this->httpRequestVerifier;
    }

    /**
     * @return GenericTokenFactoryInterface
     */
    public function getTokenFactory()
    {
        return $this->tokenFactory;
    }

    /**
     * @return StorageInterface<TokenInterface>
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }
}
