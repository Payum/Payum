<?php
namespace Payum\Core\Registry;

use Payum\Core\Extension\StorageExtension;

class SimpleRegistry extends AbstractRegistry
{
    public function registerStorageExtensions()
    {
        foreach ($this->getPayments() as $name => $payment) {
            foreach ($this->getStorages($name) as $storage) {
                $payment->addExtension(new StorageExtension($storage));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getService($id)
    {
        return $id;
    }
}