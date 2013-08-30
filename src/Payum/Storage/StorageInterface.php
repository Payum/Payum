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
     * @param object $model
     *
     * @throws \Payum\Exception\InvalidArgumentException if not supported model given.
     *
     * @return void
     */
    function deleteModel($model);

    /**
     * @param mixed $id
     * 
     * @return object|null
     */
    function findModelById($id);

    /**
     * @param Identificator $identificator
     *
     * @return object|null
     */
    function findModelByIdentificator(Identificator $identificator);

    /**
     * @param object $model
     *
     * @throws \Payum\Exception\InvalidArgumentException if not supported model given.
     *
     * @return Identificator
     */
    function getIdentificator($model);
}
