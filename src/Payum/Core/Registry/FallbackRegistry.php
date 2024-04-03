<?php

namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Storage\StorageInterface;

/**
 * @template T of object
 * @implements RegistryInterface<T>
 */
class FallbackRegistry implements RegistryInterface
{
    /**
     * @var RegistryInterface<T>
     */
    private RegistryInterface $registry;

    /**
     * @var RegistryInterface<T>
     */
    private RegistryInterface $fallbackRegistry;

    /**
     * @param RegistryInterface<T> $registry
     * @param RegistryInterface<T> $fallbackRegistry
     */
    public function __construct(RegistryInterface $registry, RegistryInterface $fallbackRegistry)
    {
        $this->registry = $registry;
        $this->fallbackRegistry = $fallbackRegistry;
    }

    public function getGatewayFactory(string $name): GatewayFactoryInterface
    {
        try {
            return $this->registry->getGatewayFactory($name);
        } catch (InvalidArgumentException) {
            return $this->fallbackRegistry->getGatewayFactory($name);
        }
    }

    /**
     * @return GatewayFactoryInterface[]
     */
    public function getGatewayFactories(): array
    {
        return array_replace($this->fallbackRegistry->getGatewayFactories(), $this->registry->getGatewayFactories());
    }

    public function getGateway(string $name): GatewayInterface
    {
        try {
            return $this->registry->getGateway($name);
        } catch (InvalidArgumentException) {
            return $this->fallbackRegistry->getGateway($name);
        }
    }

    public function getGateways(): array
    {
        return array_replace($this->fallbackRegistry->getGateways(), $this->registry->getGateways());
    }

    /**
     * @param class-string|T $class
     * @return StorageInterface<T>
     */
    public function getStorage(string | object $class): StorageInterface
    {
        try {
            return $this->registry->getStorage($class);
        } catch (InvalidArgumentException) {
            return $this->fallbackRegistry->getStorage($class);
        }
    }

    /**
     * @return array<class-string, T>>
     */
    public function getStorages(): array
    {
        return array_replace($this->fallbackRegistry->getStorages(), $this->registry->getStorages());
    }
}
