<?php

namespace Payum\Core\Storage;

use Payum\Core\Exception\InvalidArgumentException;

/**
 * @template T of object
 * @implements StorageInterface<T>
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var class-string<T>
     */
    protected string $modelClass;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function create(): object
    {
        return new $this->modelClass();
    }

    public function support(object $model): bool
    {
        return $model instanceof $this->modelClass;
    }

    public function update(object $model): object
    {
        $this->assertModelSupported($model);

        return $this->doUpdateModel($model);
    }

    public function find($id): ?object
    {
        if ($id instanceof IdentityInterface) {
            if (ltrim($id->getClass(), '\\') === ltrim($this->modelClass, '\\')) {
                return $this->doFind($id->getId());
            }

            return null;
        }

        return $this->doFind($id);
    }

    public function delete(object $model): void
    {
        $this->assertModelSupported($model);

        $this->doDeleteModel($model);
    }

    public function identify($model): IdentityInterface
    {
        $this->assertModelSupported($model);

        return $this->doGetIdentity($model);
    }

    abstract protected function doUpdateModel(object $model): object;

    abstract protected function doDeleteModel(object $model);

    abstract protected function doGetIdentity(object $model): IdentityInterface;

    abstract protected function doFind(mixed $id): ?object;

    /**
     * @throws InvalidArgumentException
     */
    protected function assertModelSupported(object $model): void
    {
        if (! $this->support($model)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s but it is %s',
                $this->modelClass,
                get_debug_type($model)
            ));
        }
    }
}
