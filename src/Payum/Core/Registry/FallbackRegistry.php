<?php

namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;

class FallbackRegistry implements RegistryInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var RegistryInterface
     */
    private $fallbackRegistry;

    public function __construct(RegistryInterface $registry, RegistryInterface $fallbackRegistry)
    {
        $this->registry = $registry;
        $this->fallbackRegistry = $fallbackRegistry;
    }

    public function getGatewayFactory($name)
    {
        try {
            return $this->registry->getGatewayFactory($name);
        } catch (InvalidArgumentException) {
            return $this->fallbackRegistry->getGatewayFactory($name);
        }
    }

    public function getGatewayFactories()
    {
        return array_replace($this->fallbackRegistry->getGatewayFactories(), $this->registry->getGatewayFactories());
    }

    public function getGateway($name)
    {
        try {
            return $this->registry->getGateway($name);
        } catch (InvalidArgumentException) {
            return $this->fallbackRegistry->getGateway($name);
        }
    }

    public function getGateways()
    {
        return array_replace($this->fallbackRegistry->getGateways(), $this->registry->getGateways());
    }

    public function getStorage($class)
    {
        try {
            return $this->registry->getStorage($class);
        } catch (InvalidArgumentException) {
            return $this->fallbackRegistry->getStorage($class);
        }
    }

    public function getStorages()
    {
        return array_replace($this->fallbackRegistry->getStorages(), $this->registry->getStorages());
    }
}
