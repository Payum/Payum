<?php
namespace Payum\Storage;

use Payum\Exception\LogicException;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class NullStorage implements StorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function createModel()
    {
        throw new LogicException('The null storage cannot create a model. This method should not be called for this storage. Please check your logic.');
    }

    /**
     * {@inheritdoc}
     */
    public function updateModel($model)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function findModelById($id)
    {
    }
}