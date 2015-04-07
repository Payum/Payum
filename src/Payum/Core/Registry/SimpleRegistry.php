<?php
namespace Payum\Core\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;

class SimpleRegistry extends AbstractRegistry
{
    /**
     * @var boolean[]
     */
    protected $initializedStorageExtensions;

    /**
     * {@inheritDoc}
     */
    public function getGateway($name)
    {
        $gateway = parent::getGateway($name);

        $this->addStorageToGateway($name);

        return $gateway;
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
    protected function addStorageToGateway($name)
    {
        if (isset($this->initializedStorageExtensions[$name])) {
            return;
        }

        $this->initializedStorageExtensions[$name] = true;

        $gateway = $this->getGateway($name);
        if (false == $gateway instanceof Gateway) {
            return;
        }

        foreach ($this->getStorages() as $storage) {
            $gateway->addExtension(new StorageExtension($storage));
        }
    }
}
