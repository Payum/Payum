<?php
namespace Payum\Domain\Storage;

interface ModelStorageInterface
{
    /**
     * @return object
     */
    function createModel();

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