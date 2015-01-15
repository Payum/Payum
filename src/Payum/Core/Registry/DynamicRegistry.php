<?php
namespace Payum\Core\Registry;

use Payum\Core\Model\PaymentConfigInterface;
use Payum\Core\Storage\StorageInterface;

class DynamicRegistry implements RegistryInterface
{
    /**
     * @var StorageInterface
     */
    private $paymentConfigStore;

    /**
     * @var RegistryInterface
     */
    private $staticRegistry;

    /**
     * @param StorageInterface $paymentConfigStore
     * @param RegistryInterface $staticRegistry
     */
    public function __construct(StorageInterface $paymentConfigStore, RegistryInterface $staticRegistry)
    {
        $this->paymentConfigStore = $paymentConfigStore;
        $this->staticRegistry = $staticRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentFactory($name)
    {
        return $this->staticRegistry->getPaymentFactory($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentFactories()
    {
        return $this->staticRegistry->getPaymentFactories();
    }

    /**
     * {@inheritDoc}
     */
    public function getPayment($name)
    {
        /** @var PaymentConfigInterface $config */
        if ($config = $this->paymentConfigStore->findBy(array('paymentName' => $name))) {
            $factory = $this->getPaymentFactory($config->getFactoryName());

            return $factory->create($config->getConfig());
        }

        return $this->staticRegistry->getPayment($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getPayments()
    {
        return $this->staticRegistry->getPayments();
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
