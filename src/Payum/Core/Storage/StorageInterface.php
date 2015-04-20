<?php
namespace Payum\Core\Storage;

interface StorageInterface
{
    /**
     * @return object
     */
    public function create();

    /**
     * @param object $model
     *
     * @return boolean
     */
    public function support($model);

    /**
     * @param object $model
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if not supported model given.
     *
     * @return void
     */
    public function update($model);

    /**
     * @param object $model
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if not supported model given.
     *
     * @return void
     */
    public function delete($model);

    /**
     * @param mixed|IdentityInterface $id
     *
     * @return object|null
     */
    public function find($id);

    /**
     * @param array $criteria
     *
     * @return object[]
     */
    public function findBy(array $criteria);

    /**
     * @param object $model
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if not supported model given.
     *
     * @return IdentityInterface
     */
    public function identify($model);
}
