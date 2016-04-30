<?php
namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Storage\StorageInterface;

class DynamicRegistry implements RegistryInterface
{
    /**
     * @var GatewayInterface[]
     */
    private $gateways = [];

    /**
     * @var StorageInterface
     */
    private $gatewayConfigStore;

    /**
     * @var GatewayFactoryRegistryInterface|null
     */
    private $gatewayFactoryRegistry;

    /**
     * @deprecated since 1.3.3 will be removed in 2.0
     *
     * @var bool
     */
    private $backwardCompatibility = true;

    /**
     * @param StorageInterface $gatewayConfigStore
     * @param GatewayFactoryRegistryInterface $gatewayFactoryRegistry
     */
    public function __construct(StorageInterface $gatewayConfigStore, GatewayFactoryRegistryInterface $gatewayFactoryRegistry)
    {
        $this->gatewayConfigStore = $gatewayConfigStore;
        $this->gatewayFactoryRegistry = $gatewayFactoryRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayFactory($name)
    {
        // @deprecated It will throw invalid argument exception in 2.x
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof  RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGatewayFactory($name);
        }

        throw new InvalidArgumentException(sprintf('Gateway factory "%s" does not exist.', $name));
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayFactories()
    {
        // @deprecated It will return empty array here
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof  RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGatewayFactories();
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getGateway($name)
    {
        if (array_key_exists($name, $this->gateways)) {
            return $this->gateways[$name];
        }

        /** @var GatewayConfigInterface[] $configs */
        if ($configs = $this->gatewayConfigStore->findBy(array('gatewayName' => $name))) {
            $config = array_shift($configs);

            $factory = $this->gatewayFactoryRegistry->getGatewayFactory($config->getFactoryName());

            $gateway = $factory->create($config->getConfig());
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
     * {@inheritDoc}
     */
    public function getGateways()
    {
        // @deprecated It will return empty array here
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof  RegistryInterface) {
            return $this->gatewayFactoryRegistry->getGateways();
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getStorage($class)
    {
        // @deprecated It will throw invalid argument exception in 2.x
        if ($this->backwardCompatibility && $this->gatewayFactoryRegistry instanceof RegistryInterface) {
            return $this->gatewayFactoryRegistry->getStorage($class);
        }

        throw new InvalidArgumentException(sprintf('Storage for given class "%s" does not exist.', $class));
    }

    /**
     * {@inheritDoc}
     */
    public function getStorages()
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
    public function setBackwardCompatibility($backwardCompatibility)
    {
        $this->backwardCompatibility = $backwardCompatibility;
    }
}
