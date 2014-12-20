<?php
namespace Payum\Core\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Payment;

class SimpleRegistry extends AbstractRegistry
{
    /**
     * @var boolean[]
     */
    protected $initializedStorageExtensions;

    /**
     * {@inheritDoc}
     */
    public function getPayment($name = null)
    {
        $payment = parent::getPayment($name);

        $this->addStorageToPayment($name);

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    protected function getService($id)
    {
        return $id;
    }

    /**
     * @param string|null $name
     */
    protected function addStorageToPayment($name)
    {
        if (null === $name) {
            $name = $this->defaultPayment;
        }

        if (isset($this->initializedStorageExtensions[$name])) {
            return;
        }

        $this->initializedStorageExtensions[$name] = true;

        $payment = $this->getPayment($name);
        if (false == $payment instanceof Payment) {
            return;
        }

        foreach ($this->getStorages() as $storage) {
            $payment->addExtension(new StorageExtension($storage));
        }
    }
}
