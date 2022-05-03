<?php
namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Storage\StorageInterface;

interface StorageRegistryInterface
{
    /**
     * @throws InvalidArgumentException if storage with such name not exists
     */
    public function getStorage(object|string $class): StorageInterface;

    /**
     * The key must be a model class
     *
     * @return StorageInterface[]
     */
    public function getStorages(): array;
}
