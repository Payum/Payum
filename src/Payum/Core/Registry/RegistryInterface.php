<?php

namespace Payum\Core\Registry;

/**
 * @template T of object
 * @extends StorageRegistryInterface<T>
 */
interface RegistryInterface extends GatewayRegistryInterface, GatewayFactoryRegistryInterface, StorageRegistryInterface
{
}
