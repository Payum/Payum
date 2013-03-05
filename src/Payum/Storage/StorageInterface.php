<?php
namespace Payum\Storage;

interface StorageInterface
{
    /**
     * @return object
     */
    function createModel();

    /**
     * @param object $model
     * 
     * @return boolean
     */
    function supportModel($model);

    /**
     * @param object $model
     * 
     * @throws \Payum\Exception\InvalidArgumentException if not supported model given. 
     *
     * @return void
     */
    function updateModel($model);

    /**
     * @param mixed $id
     * 
     * @return object|null
     */
    function findModelById($id);
}