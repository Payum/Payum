<?php
namespace Payum\Core\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;
use Payum\Core\GatewayInterface;

class SimpleRegistry extends AbstractRegistry
{
    /**
     * @var boolean[]
     */
    protected $initializedStorageExtensions;

    /**
     * @deprecated since 1.3.3 and ill be removed in 2.x. It is here for BC
     *
     * @var bool
     */
    protected $addStorageExtensions = true;

    /**
     * @deprecated since 1.3.3 and will be removed in 2.x. It is here for BC
     *
     * @param boolean $bool
     */
    public function setAddStorageExtensions($bool)
    {
        $this->addStorageExtensions = $bool;
    }

    /**
     * {@inheritDoc}
     */
    public function getGateway($name)
    {
        $gateway = parent::getGateway($name);

        if ($this->addStorageExtensions) {
            $this->addStorageToGateway($name, $gateway);
        }

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
     * @deprecated since 1.3.3 and will be removed in 2.x.
     *
     * @param string           $name
     * @param GatewayInterface $gateway
     */
    protected function addStorageToGateway($name, GatewayInterface $gateway)
    {
        /** @var Gateway $gateway */
        if (false == $gateway instanceof Gateway) {
            return;
        }
        if (isset($this->initializedStorageExtensions[$name])) {
            return;
        }

        foreach ($this->getStorages() as $storage) {
            $gateway->addExtension(new StorageExtension($storage));
        }

        $this->initializedStorageExtensions[$name] = true;
    }
}
