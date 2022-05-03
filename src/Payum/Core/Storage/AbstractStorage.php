<?php
namespace Payum\Core\Storage;

use Payum\Core\Exception\InvalidArgumentException;

abstract class AbstractStorage implements StorageInterface
{
    public function __construct(protected string $modelClass)
    {}

    /**
     * {@inheritDoc}
     */
    public function create()
    {
        return new $this->modelClass();
    }

    /**
     * {@inheritDoc}
     */
    public function support($model): bool
    {
        return $model instanceof $this->modelClass;
    }

    /**
     * {@inheritDoc}
     */
    public function update($model): void
    {
        $this->assertModelSupported($model);

        $this->doUpdateModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function find($id)
    {
        if ($id instanceof IdentityInterface) {
            if (ltrim($id->getClass(), '\\') === ltrim($this->modelClass, '\\')) {
                return $this->doFind($id->getId());
            }

            return;
        }

        return $this->doFind($id);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($model): void
    {
        $this->assertModelSupported($model);

        $this->doDeleteModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function identify($model): IdentityInterface
    {
        $this->assertModelSupported($model);

        return $this->doGetIdentity($model);
    }

    /**
     * @param object $model
     */
    abstract protected function doUpdateModel(object $model): void;

    /**
     * @param object $model
     */
    abstract protected function doDeleteModel($model): void;

    /**
     * @param object $model
     */
    abstract protected function doGetIdentity($model): IdentityInterface;

    /**
     * @param mixed $id
     */
    abstract protected function doFind($id): ?object;

    /**
     * @throws \Payum\Core\Exception\InvalidArgumentException
     */
    protected function assertModelSupported(object $model): void
    {
        if (false == $this->support($model)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s but it is %s',
                $this->modelClass,
                is_object($model) ? get_class($model) : gettype($model)
            ));
        }
    }
}
