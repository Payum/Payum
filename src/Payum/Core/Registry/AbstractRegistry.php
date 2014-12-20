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
     * @param array  $payments
     * @param array  $storages
     * @param string $defaultPayment
     */
    public function __construct($payments, $storages, $defaultPayment = 'default')
    {
        $this->payments = $payments;
        $this->storages = $storages;

        $this->defaultPayment = $defaultPayment;
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
    public function getStorage($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        // TODO: this is a quick fix. I have to find a better\clean solution.
        if (class_exists($class) && interface_exists('Doctrine\Common\Persistence\Proxy')) {
            $rc = new \ReflectionClass($class);
            if ($rc->implementsInterface('Doctrine\Common\Persistence\Proxy')) {
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
        $registeredPayments = array();
        foreach ($this->payments as $name => $id) {
            $registeredPayments[$name] = $this->getPayment($name);
        }

        return $registeredPayments;
    }
}
