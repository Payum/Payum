<?php
namespace Payum\Registry;

use Payum\Extension\StorageExtension;

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