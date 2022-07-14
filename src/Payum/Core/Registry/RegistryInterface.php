<?php

namespace Payum\Core\Registry;

/**
 * @template StorageType of object
 * @extends StorageRegistryInterface<StorageType>
 */
interface RegistryInterface extends GatewayRegistryInterface, GatewayFactoryRegistryInterface, StorageRegistryInterface
{
}
