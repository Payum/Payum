<?php

namespace Payum\Core\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;
use Payum\Core\GatewayInterface;

/**
 * @template T of object
 * @extends AbstractRegistry<T>
 */
class SimpleRegistry extends AbstractRegistry
{
    /**
     * @var boolean[]
     */
    protected array $initializedStorageExtensions;

    /**
     * @deprecated since 1.3.3 and ill be removed in 2.x. It is here for BC
     */
    protected bool $addStorageExtensions = true;

    /**
     * @deprecated since 1.3.3 and will be removed in 2.x. It is here for BC
     *
     * @param boolean $bool
     */
    public function setAddStorageExtensions($bool): void
    {
        $this->addStorageExtensions = $bool;
    }

    public function getGateway(string $name): GatewayInterface
    {
        $gateway = parent::getGateway($name);

        if ($this->addStorageExtensions) {
            $this->addStorageToGateway($name, $gateway);
        }

        return $gateway;
    }

    protected function getService($id): object | string
    {
        return $id;
    }

    /**
     * @deprecated since 1.3.3 and will be removed in 2.x.
     */
    protected function addStorageToGateway(string $name, GatewayInterface $gateway): void
    {
        /** @var Gateway $gateway */
        if (isset($this->initializedStorageExtensions[$name])) {
            return;
        }

        foreach ($this->getStorages() as $storage) {
            $gateway->addExtension(new StorageExtension($storage));
        }

        $this->initializedStorageExtensions[$name] = true;
    }
}
