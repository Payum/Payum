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
        if (false == is_a($identificator->getClass(), $this->modelClass, $allowClass = true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s but it is %s',
                $this->modelClass,
                $identificator->getClass()
            ));
        }

        return $this->doFindModelByIdentificator($identificator);
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
     * @param Identificator $identificator
     *
     * @return object|null
     */
    abstract protected function doFindModelByIdentificator(Identificator $identificator);

    /**
     * @param mixed $model
     *
     * @throws \Payum\Exception\InvalidArgumentException
     */
    protected function assertModelSupported($model)
    {
        if (false == $model instanceof $this->modelClass) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s but it is %s',
                $this->modelClass,
                is_object($model) ? get_class($model) : gettype($model)
            ));
        }
    }
}