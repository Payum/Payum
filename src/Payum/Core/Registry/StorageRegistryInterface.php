<?php
namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Storage\StorageInterface;

interface StorageRegistryInterface
{
    /**
     * @param object|string $class
     *
     * @throws InvalidArgumentException if storage with such name not exists
     *
     * @return StorageInterface
     */
    public function getStorage($class);

    /**
     * @throws InvalidArgumentException if storages with such name not exist
     *
     * @return StorageInterface
     */
    public function getStorages();
}
