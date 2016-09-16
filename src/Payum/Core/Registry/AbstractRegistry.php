<?php
namespace Payum\Core\Registry;

use Doctrine\Common\Persistence\Proxy;
use Payum\Core\Exception\InvalidArgumentException;

abstract class AbstractRegistry implements RegistryInterface
{
    /**
     * @var array
     */
    protected $gateways;

    /**
     * @var array
     */
    protected $storages;

    /**
     * @var array
     */
    protected $gatewayFactories;

    /**
     * @param array $gateways
     * @param array $storages
     * @param array $gatewayFactories
     */
    public function __construct(array $gateways = array(), array $storages = array(), array $gatewayFactories = array())
    {
        $this->gateways = $gateways;
        $this->storages = $storages;
        $this->gatewayFactories = $gatewayFactories;
    }

    /**
     * Fetches/creates the given services
     *
     * A service in this context is a storage or a gateway or gateway factory instance
     *
     * @param string $id name of the service
     *
     * @return object instance of the given service
     */
    abstract protected function getService($id);

    /**
     * {@inheritDoc}
     */
    public function getStorage($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        // TODO: this is a quick fix. I have to find a better\clean solution.
        if (class_exists($class) && interface_exists(Proxy::class)) {
            $rc = new \ReflectionClass($class);
            if ($rc->implementsInterface(Proxy::class)) {
                $class = $rc->getParentClass()->getName();
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
    public function getStorages()
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
    public function getGateway($name)
    {
        if (!isset($this->gateways[$name])) {
            throw new InvalidArgumentException(sprintf('Gateway "%s" does not exist.', $name));
        }

        return $this->getService($this->gateways[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getGateways()
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
    public function getGatewayFactory($name)
    {
        if (!isset($this->gatewayFactories[$name])) {
            throw new InvalidArgumentException(sprintf('Gateway factory "%s" does not exist.', $name));
        }

        return $this->getService($this->gatewayFactories[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getGatewayFactories()
    {
        $gatewayFactories = array();
        foreach ($this->gatewayFactories as $name => $id) {
            $gatewayFactories[$name] = $this->getGatewayFactory($name);
        }

        return $gatewayFactories;
    }
}
