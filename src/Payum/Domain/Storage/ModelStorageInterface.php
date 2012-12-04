<?php
namespace Payum\Domain\Storage;

use Payum\Domain\ModelInterface;

interface ModelStorageInterface
{
    /**
     * @return \Payum\Domain\ModelInterface
     */
    function createModel();

    /**
     * @param \Payum\Domain\ModelInterface $model
     * 
     * @throws \Payum\Exception\InvalidArgumentException if not supported model given. 
     *
     * @return void
     */
    function updateModel(ModelInterface $model);

    /**
     * @param mixed $id
     * 
     * @return \Payum\Domain\ModelInterface|null
     */
    function findModelById($id);
}