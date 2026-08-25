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
     * @var bool[]
     */
    protected array $initializedStorageExtensions;

    /**
     * @deprecated since 2.0.0, will be removed in 3.0 together with
     *             Payum\Core\Extension\StorageExtension, which a gateway built from handlers does not
     *             get — it persists through Payum\Core\Middleware\PersistStateMiddleware instead.
     */
    protected bool $addStorageExtensions = true;

    /**
     * @deprecated since 2.0.0, will be removed in 3.0 together with
     *             Payum\Core\Extension\StorageExtension, which a gateway built from handlers does not
     *             get — it persists through Payum\Core\Middleware\PersistStateMiddleware instead.
     *
     * @param bool $bool
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
     * @deprecated since 2.0.0, will be removed in 3.0 together with
     *             Payum\Core\Extension\StorageExtension, which a gateway built from handlers does not
     *             get — it persists through Payum\Core\Middleware\PersistStateMiddleware instead.
     */
    protected function addStorageToGateway(string $name, GatewayInterface $gateway): void
    {
        if (isset($this->initializedStorageExtensions[$name])) {
            return;
        }

        foreach ($this->getStorages() as $storage) {
            if ($gateway instanceof Gateway) {
                $gateway->addExtension(new StorageExtension($storage));
            }
        }

        $this->initializedStorageExtensions[$name] = true;
    }
}
