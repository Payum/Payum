<?php

namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Storage\StorageInterface;

/**
 * @template StorageType of object
 * @implements RegistryInterface<StorageType>
 */
class FallbackRegistry implements RegistryInterface
{
    /**
     * @var RegistryInterface<StorageType>
     */
    private RegistryInterface $registry;

    /**
     * @var RegistryInterface<StorageType>
     */
    private RegistryInterface $fallbackRegistry;

    /**
     * @param RegistryInterface<StorageType> $registry
     * @param RegistryInterface<StorageType> $fallbackRegistry
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
        } catch (InvalidArgumentException $e) {
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
        } catch (InvalidArgumentException $e) {
            return $this->fallbackRegistry->getGateway($name);
        }
    }

    /**
     * @return GatewayInterface[]
     */
    public function getGateways(): array
    {
        return array_replace($this->fallbackRegistry->getGateways(), $this->registry->getGateways());
    }

    public function getStorage(string $class): StorageInterface
    {
        try {
            return $this->registry->getStorage($class);
        } catch (InvalidArgumentException $e) {
            return $this->fallbackRegistry->getStorage($class);
        }
    }

    /**
     * @return array<class-string, StorageInterface<StorageType>>
     */
    public function getStorages(): array
    {
        return array_replace($this->fallbackRegistry->getStorages(), $this->registry->getStorages());
    }
}
