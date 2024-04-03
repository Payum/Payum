<?php

namespace Payum\Core\Storage;

use Payum\Core\Exception\InvalidArgumentException;

/**
 * @template T of object
 */
interface StorageInterface
{
    /**
     * @return T
     */
    public function create(): object;

    /**
     * @param T $model
     *
     * @return boolean
     */
    public function support(object $model): bool;

    /**
     * @param T $model
     * @return T
     *
     * @throws InvalidArgumentException if not supported model given.
     */
    public function update(object $model): object;

    /**
     * @param T $model
     *
     * @throws InvalidArgumentException if not supported model given.
     */
    public function delete(object $model);

    /**
     * @param mixed|IdentityInterface $id
     *
     * @return ?T
     */
    public function find($id);

    /**
     * @return T[]
     */
    public function findBy(array $criteria);

    /**
     * @param object $model
     *
     * @throws InvalidArgumentException if not supported model given.
     *
     * @return IdentityInterface
     */
    public function identify($model);
}
