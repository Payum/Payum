<?php
namespace Payum\Core\Registry;

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
     * @var RegistryInterface
     */
    private $staticRegistry;

    /**
     * @param StorageInterface $gatewayConfigStore
     * @param RegistryInterface $staticRegistry
     */
    public function __construct(StorageInterface $gatewayConfigStore, RegistryInterface $staticRegistry)
    {
        $this->gatewayConfigStore = $gatewayConfigStore;
        $this->staticRegistry = $staticRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayFactory($name)
    {
        return $this->staticRegistry->getGatewayFactory($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayFactories()
    {
        return $this->staticRegistry->getGatewayFactories();
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

            $factory = $this->getGatewayFactory($config->getFactoryName());

            $gateway = $factory->create($config->getConfig());
            $this->gateways[$name] = $gateway;

            return $gateway;
        }

        return $this->staticRegistry->getGateway($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getGateways()
    {
        return $this->staticRegistry->getGateways();
    }

    /**
     * {@inheritDoc}
     */
    public function getStorage($class)
    {
        return $this->staticRegistry->getStorage($class);
    }

    /**
     * {@inheritDoc}
     */
    public function getStorages()
    {
        return $this->staticRegistry->getStorages();
    }
}
