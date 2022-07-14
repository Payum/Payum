<?php

namespace Payum\Core\Registry;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Storage\StorageInterface;

/**
 * @template T of object
 */
interface StorageRegistryInterface
{
    /**
     * @param class-string<T> $class
     *
     * @return StorageInterface<T>
     *
     * @throws InvalidArgumentException if storage with such name not exists
     */
    public function getStorage(string $class): StorageInterface;

    /**
     * The key must be a model class
     *
     * @return array<class-string, StorageInterface<T>>
     */
    public function getStorages(): array;
}
