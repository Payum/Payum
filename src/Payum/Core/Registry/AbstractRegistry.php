<?php
namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;

abstract class AbstractRegistry implements RegistryInterface
{
    /**
     * @var array
     */
    protected $payments;

    /**
     * @var array
     */
    protected $storages;

    /**
     * @var string
     */
    protected $defaultPayment;

    /**
     * @var string
     */
    protected $defaultStorage;
    
    /**
     * @param array $payments
     * @param array $storages
     * @param string $defaultPayment
     * @param string $defaultStorage
     */
    public function __construct($payments, $storages, $defaultPayment = 'default', $defaultStorage = 'default')
    {
        $this->payments = $payments;
        $this->storages = $storages;
        
        $this->defaultPayment = $defaultPayment;
        $this->defaultStorage = $defaultStorage;
    }

    /**
     * Fetches/creates the given services
     *
     * A service in this context is a storage or a payment instance
     *
     * @param string $id name of the service
     *
     * @return object instance of the given service
     */
    abstract protected function getService($id);
    
    /**
     * {@inheritDoc}
     */
    public function getDefaultStorageName()
    {
        return $this->defaultStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function getStorageForClass($class, $name = null)
    {
        if (null === $name) {
            $name = $this->defaultStorage;
        }

        if (false == isset($this->storages[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Any storages for payment %s were not registered. Registered payments: %s.',
                $name,
                implode(', ', array_keys($this->storages))
            ));
        }

        $class = is_object($class) ? get_class($class) : $class;

        if (!isset($this->storages[$name][$class])) {
            throw new InvalidArgumentException(sprintf(
                'A storage for payment %s and model %s was not registered. The payment supports storages for next models: %s.',
                $name,
                $class,
                implode(', ', array_keys($this->storages[$name]))
            ));
        }

        return $this->getService($this->storages[$name][$class]);
    }

    /**
     * {@inheritDoc}
     */
    public function getStorages($name = null)
    {
        if (null === $name) {
            $name = $this->defaultStorage;
        }

        if (!isset($this->storages[$name])) {
            throw new InvalidArgumentException(sprintf('Payum storages named %s do not exist.', $name));
        }

        $storages = array();
        foreach ($this->storages[$name] as $modelClass => $storageId) {
            $storages[$modelClass] = $this->getService($storageId);
        }

        return $storages;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultPaymentName()
    {
        return $this->defaultPayment;
    }

    /**
     * {@inheritDoc}
     */
    public function getPayment($name = null)
    {
        if (null === $name) {
            $name = $this->defaultPayment;
        }

        if (!isset($this->payments[$name])) {
            throw new InvalidArgumentException(sprintf('Payum payment named %s does not exist.', $name));
        }

        return $this->getService($this->payments[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getPayments()
    {
        $connections = array();
        foreach ($this->payments as $name => $id) {
            $connections[$name] = $this->getService($id);
        }

        return $connections;
    }
}