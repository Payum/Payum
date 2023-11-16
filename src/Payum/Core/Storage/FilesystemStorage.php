<?php

namespace Payum\Core\Storage;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use ReflectionObject;
use ReflectionProperty;

/**
 * @template T of object
 * @extends AbstractStorage<T>
 */
class FilesystemStorage extends AbstractStorage
{
    protected string $storageDir;

    protected string $idProperty;

    protected array $identityMap;

    public function __construct(string $storageDir, string $modelClass, string $idProperty = 'payum_id')
    {
        parent::__construct($modelClass);

        $this->storageDir = $storageDir;
        $this->idProperty = $idProperty;
    }

    public function findBy(array $criteria): array
    {
        throw new LogicException('Method is not supported by the storage.');
    }

    protected function doFind(mixed $id): ?object
    {
        if (isset($this->identityMap[$id])) {
            return $this->identityMap[$id];
        }

        if (file_exists($this->storageDir . '/payum-model-' . $id)) {
            return $this->identityMap[$id] = unserialize(file_get_contents($this->storageDir . '/payum-model-' . $id));
        }

        return null;
    }

    protected function doUpdateModel(object $model): object
    {
        $ro = new ReflectionObject($model);

        if (! $ro->hasProperty($this->idProperty)) {
            $model->{$this->idProperty} = null;
        }

        $rp = new ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        $id = $rp->getValue($model);
        if (! $id) {
            $rp->setValue($model, $id = uniqid('', true));
        }

        $rp->setAccessible(false);

        $this->identityMap[$id] = $model;
        file_put_contents($this->storageDir . '/payum-model-' . $id, serialize($model));

        return $model;
    }

    protected function doDeleteModel(object $model): void
    {
        $rp = new ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        if ($id = $rp->getValue($model)) {
            unlink($this->storageDir . '/payum-model-' . $id);
            unset($this->identityMap[$id]);
        }
    }

    protected function doGetIdentity(object $model): Identity
    {
        $rp = new ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        if (! $id = $rp->getValue($model)) {
            throw new LogicException('The model must be persisted before usage of this method');
        }

        return new Identity($id, $model);
    }
}
