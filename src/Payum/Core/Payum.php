<?php
namespace Payum\Core;

use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;

class Payum implements RegistryInterface
{
    public function __construct(
        protected RegistryInterface            $registry,
        protected HttpRequestVerifierInterface $httpRequestVerifier,
        protected GenericTokenFactoryInterface $tokenFactory,
        protected StorageInterface $tokenStorage
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayFactory($name): GatewayFactoryInterface
    {
        return $this->registry->getGatewayFactory($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayFactories(): array
    {
        return $this->registry->getGatewayFactories();
    }

    /**
     * {@inheritDoc}
     */
    public function getGateway(string $name): GatewayInterface
    {
        return $this->registry->getGateway($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getGateways(): array
    {
        return $this->registry->getGateways();
    }

    /**
     * {@inheritDoc}
     */
    public function getStorage($class): StorageInterface
    {
        return $this->registry->getStorage($class);
    }

    /**
     * {@inheritDoc}
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

    public function getTokenStorage(): StorageInterface
    {
        return $this->tokenStorage;
    }
}
