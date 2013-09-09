<?php
namespace Payum\Storage;

use Payum\Exception\InvalidArgumentException;
use Payum\Exception\LogicException;
use Payum\Storage\StorageInterface;

abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @param $modelClass
     */
    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * {@inheritDoc}
     */
    public function createModel()
    {
        return new $this->modelClass;
    }

    /**
     * {@inheritDoc}
     */
    public function supportModel($model)
    {
        return $model instanceof $this->modelClass;
    }

    /**
     * {@inheritDoc}
     */
    public function updateModel($model)
    {
        $this->assertModelSupported($model);

        $this->doUpdateModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteModel($model)
    {
        $this->assertModelSupported($model);

        $this->doDeleteModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function findModelByIdentificator(Identificator $identificator)
    {
        if (is_a($identificator->getClass(), $this->modelClass, $allowClass = true)) {
            return $this->findModelById($identificator->getId());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentificator($model)
    {
        $this->assertModelSupported($model);

        return $this->doGetIdentificator($model);
    }

    /**
     * @param object $model
     *
     * @return void
     */
    abstract protected function doUpdateModel($model);

    /**
     * @param object $model
     *
     * @return void
     */
    abstract protected function doDeleteModel($model);

    /**
     * @param object $model
     *
     * @return Identificator
     */
    abstract protected function doGetIdentificator($model);

    /**
     * @param object $model
     *
     * @throws \Payum\Exception\InvalidArgumentException
     */
    protected function assertModelSupported($model)
    {
        if (false == $this->supportModel($model)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s but it is %s',
                $this->modelClass,
                is_object($model) ? get_class($model) : gettype($model)
            ));
        }
    }
}