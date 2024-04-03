<?php

namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Storage\StorageInterface;

/**
 * @template StorageType of object
 * @implements RegistryInterface<StorageType>
 */
class DynamicRegistry implements RegistryInterface
{
    /**
     * @var GatewayInterface[]
     */
    private array $gateways = [];

    /**
     * @var StorageInterface<GatewayConfigInterface>
     */
    private StorageInterface $gatewayConfigStore;

    /**
     * @var GatewayFactoryRegistryInterface|RegistryInterface<StorageType>
     */
    private GatewayFactoryRegistryInterface | RegistryInterface $gatewayFactoryRegistry;

    /**
     * @deprecated since 1.3.3 will be removed in 2.0
     */
    private bool $backwardCompatibility = true;

    /**
     * @param StorageInterface<GatewayConfigInterface> $gatewayConfigStore
     */
    public function __construct(StorageInterface $gatewayConfigStore, GatewayFactoryRegistryInterface $gatewayFactoryRegistry)
    {
        $this->gatewayConfigStore = $gatewayConfigStore;
        $this->gatewayFactoryRegistry = $gatewayFactoryRegistry;
    }

    public function getGatewayFactory(string $name): GatewayFactoryInterface
    {
        // @deprecated It will throw invalid argument exception in 2.x
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGatewayFactory($name);
        }

        throw new InvalidArgumentException(sprintf('Gateway factory "%s" does not exist.', $name));
    }

    public function getGatewayFactories(): array
    {
        // @deprecated It will return empty array here
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGatewayFactories();
        }

        return [];
    }

    public function getGateway(string $name): GatewayInterface
    {
        if (array_key_exists($name, $this->gateways)) {
            return $this->gateways[$name];
        }

        if ($gatewayConfigs = $this->gatewayConfigStore->findBy([
            'gatewayName' => $name,
        ])) {
            $gateway = $this->createGateway(array_shift($gatewayConfigs));
            $this->gateways[$name] = $gateway;

            return $gateway;
        }

        // @deprecated It will throw invalid argument exception in 2.x
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGateway($name);
        }

        throw new InvalidArgumentException(sprintf('Gateway "%s" does not exist.', $name));
    }

    /**
     * @return GatewayInterface[]
     */
    public function getGateways(): array
    {
        // @deprecated It will return empty array here
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGateways();
        }

        $gateways = [];
        foreach ($this->gatewayConfigStore->findBy([]) as $gatewayConfig) {
            /** @var GatewayConfigInterface $gatewayConfig */

            $gateways[$gatewayConfig->getGatewayName()] = $this->getGateway($gatewayConfig->getGatewayName());
        }

        return $gateways;
    }

    /**
     * @param class-string|object $class
     *
     * @return StorageInterface<object>
     */
    public function getStorage($class): StorageInterface
    {
        // @deprecated It will throw invalid argument exception in 2.x
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getStorage($class);
        }

        throw new InvalidArgumentException(sprintf(
            'Storage for given class "%s" does not exist.',
            is_object($class) ? $class::class : $class
        ));
    }

    /**
     * @return StorageInterface<object>[]
     */
    public function getStorages(): array
    {
        // @deprecated It will return empty array here
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getStorages();
        }

        return [];
    }

    /**
     * @deprecated since 1.3.3 will be removed in 2.0
     *
     * @param boolean $backwardCompatibility
     */
    public function setBackwardCompatibility($backwardCompatibility): void
    {
        $this->backwardCompatibility = $backwardCompatibility;
    }

    /**
     * @return GatewayInterface
     */
    protected function createGateway(GatewayConfigInterface $gatewayConfig)
    {
        $config = $gatewayConfig->getConfig();

        if (isset($config['factory'])) {
            $factory = $this->gatewayFactoryRegistry->getGatewayFactory($config['factory']);
            unset($config['factory']);
        } else {
            // BC

            $factory = $this->gatewayFactoryRegistry->getGatewayFactory($gatewayConfig->getFactoryName());
        }

        return $factory->create($config);
    }
}
