<?php
namespace Payum\Core\Storage;

interface StorageInterface
{
    /**
     * @return object
     */
    function create();

    /**
     * @param object $model
     * 
     * @return boolean
     */
    function support($model);

    /**
     * @param object $model
     * 
     * @throws \Payum\Core\Exception\InvalidArgumentException if not supported model given.
     *
     * @return void
     */
    function update($model);

    /**
     * @param object $model
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if not supported model given.
     *
     * @return void
     */
    function delete($model);

    /**
     * @param mixed|IdentityInterface $id
     * 
     * @return object|null
     */
    function find($id);

    /**
     * @param object $model
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if not supported model given.
     *
     * @return IdentityInterface
     */
    function identify($model);
}
