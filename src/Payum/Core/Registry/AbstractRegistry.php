<?php
namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Storage\StorageInterface;

abstract class AbstractRegistry implements RegistryInterface
{
    public function __construct(protected array $gateways = [], protected array $storages = [], protected array $gatewayFactories = [])
    {}

    /**
     * Fetches/creates the given services
     *
     * A service in this context is a storage or a gateway or gateway factory instance
     *
     * @param string $id name of the service
     *
     * @return object instance of the given service
     */
    abstract protected function getService(string $id): object;

    /**
     * {@inheritDoc}
     */
    public function getStorage($class): StorageInterface
    {
        $class = is_object($class) ? get_class($class) : $class;

        // TODO: this is a quick fix. I have to find a better\clean solution.
        if (class_exists($class)) {
            if (interface_exists('Doctrine\Persistence\Proxy')) {
                $rc = new \ReflectionClass($class);
                if ($rc->implementsInterface(\Doctrine\Persistence\Proxy::class)) {
                    $class = $rc->getParentClass()->getName();
                }
            } elseif (interface_exists('Doctrine\Common\Persistence\Proxy')) {
                $rc = new \ReflectionClass($class);
                if ($rc->implementsInterface(\Doctrine\Common\Persistence\Proxy::class)) {
                    $class = $rc->getParentClass()->getName();
                }
            }
        }

        if (!isset($this->storages[$class])) {
            throw new InvalidArgumentException(sprintf(
                'A storage for model %s was not registered. There are storages for next models: %s.',
                $class,
                implode(', ', array_keys($this->storages))
            ));
        }

        return $this->getService($this->storages[$class]);
    }

    /**
     * {@inheritDoc}
     */
    public function getStorages(): array
    {
        $storages = array();
        foreach ($this->storages as $modelClass => $storageId) {
            $storages[$modelClass] = $this->getService($storageId);
        }

        return $storages;
    }

    /**
     * {@inheritDoc}
     */
    public function getGateway(string $name): GatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new InvalidArgumentException(sprintf('Gateway "%s" does not exist.', $name));
        }

        return $this->getService($this->gateways[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getGateways(): array
    {
        $gateways = array();
        foreach ($this->gateways as $name => $id) {
            $gateways[$name] = $this->getGateway($name);
        }

        return $gateways;
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayFactory(string $name): GatewayFactoryInterface
    {
        if (!isset($this->gatewayFactories[$name])) {
            throw new InvalidArgumentException(sprintf('Gateway factory "%s" does not exist.', $name));
        }

        return $this->getService($this->gatewayFactories[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayFactories(): array
    {
        $gatewayFactories = array();
        foreach ($this->gatewayFactories as $name => $id) {
            $gatewayFactories[$name] = $this->getGatewayFactory($name);
        }

        return $gatewayFactories;
    }
}
