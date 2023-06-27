<?php

namespace Payum\Core\Registry;

use Doctrine\Persistence\Proxy;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Storage\StorageInterface;
use ReflectionClass;

/**
 * @template StorageType of object
 * @implements RegistryInterface<StorageType>
 */
abstract class AbstractRegistry implements RegistryInterface
{
    /**
     * @var array
     */
    protected $gateways;

    /**
     * @var array<class-string<StorageType>, string | StorageInterface<StorageType>>
     */
    protected $storages;

    /**
     * @var array
     */
    protected $gatewayFactories;

    /**
     * @param array<class-string<StorageType>, string | StorageInterface<StorageType>> $storages
     * @param mixed[] $gateways
     * @param mixed[] $gatewayFactories
     */
    public function __construct(array $gateways = [], array $storages = [], array $gatewayFactories = [])
    {
        $this->gateways = $gateways;
        $this->storages = $storages;
        $this->gatewayFactories = $gatewayFactories;
    }

    public function getStorage(string $class): StorageInterface
    {
        // TODO: this is a quick fix. I have to find a better\clean solution.
        if (class_exists($class) && interface_exists(Proxy::class)) {
            $rc = new ReflectionClass($class);
            if ($rc->implementsInterface(Proxy::class)) {
                $class = $rc->getParentClass()->getName();
            }
        }

        if (! isset($this->storages[$class])) {
            throw new InvalidArgumentException(sprintf(
                'A storage for model %s was not registered. There are storages for next models: %s.',
                $class,
                implode(', ', array_keys($this->storages))
            ));
        }

        return $this->getService($this->storages[$class]);
    }

    public function getStorages(): array
    {
        $storages = [];
        foreach ($this->storages as $modelClass => $storageId) {
            $storages[$modelClass] = $this->getService($storageId);
        }

        return $storages;
    }

    public function getGateway(string $name): GatewayInterface
    {
        if (! isset($this->gateways[$name])) {
            throw new InvalidArgumentException(sprintf('Gateway "%s" does not exist.', $name));
        }

        return $this->getService($this->gateways[$name]);
    }

    public function getGateways(): array
    {
        $gateways = [];
        foreach ($this->gateways as $name => $id) {
            $gateways[$name] = $this->getGateway($name);
        }

        return $gateways;
    }

    public function getGatewayFactory(string $name): GatewayFactoryInterface
    {
        if (! isset($this->gatewayFactories[$name])) {
            throw new InvalidArgumentException(sprintf('Gateway factory "%s" does not exist.', $name));
        }

        return $this->getService($this->gatewayFactories[$name]);
    }

    public function getGatewayFactories(): array
    {
        $gatewayFactories = [];
        foreach ($this->gatewayFactories as $name => $id) {
            $gatewayFactories[$name] = $this->getGatewayFactory($name);
        }

        return $gatewayFactories;
    }

    /**
     * Fetches/creates the given services
     *
     * A service in this context is a storage or a gateway or gateway factory instance
     *
     * @return object instance of the given service
     */
    abstract protected function getService(string $id): object;
}
