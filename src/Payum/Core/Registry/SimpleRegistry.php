<?php
namespace Payum\Core\Registry;

use Payum\Core\Extension\StorageExtension;

class SimpleRegistry extends AbstractRegistry
{
    /**
     * @var boolean[]
     */
    protected $initializedStorageExtensions;

    /**
     * @param $name
     */
    protected function initializeStorageExtensionsForPayment($name)
    {
        if (null === $name) {
            $name = $this->defaultPayment;
        }

        if (isset($this->initializedStorageExtensions[$name])) {
            return;
        }

        $this->initializedStorageExtensions[$name] = true;

        if (!isset($this->storages[$name])) {
            return;
        }

        $payment = $this->getPayment($name);
        foreach ($this->getStorages($name) as $storage) {
            $payment->addExtension(new StorageExtension($storage));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPayment($name = null)
    {
        $payment = parent::getPayment($name);

        $this->initializeStorageExtensionsForPayment($name);

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    protected function getService($id)
    {
        return $id;
    }
}