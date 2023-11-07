<?php

namespace Payum\Core\Storage;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use ReflectionObject;
use ReflectionProperty;

class FilesystemStorage extends AbstractStorage
{
    /**
     * @var string
     */
    protected $storageDir;

    /**
     * @var string
     */
    protected $idProperty;

    /**
     * @var array
     */
    protected $identityMap;

    /**
     * @param string $storageDir
     * @param string $modelClass
     * @param string $idProperty
     */
    public function __construct($storageDir, $modelClass, $idProperty = 'payum_id')
    {
        parent::__construct($modelClass);

        $this->storageDir = $storageDir;
        $this->idProperty = $idProperty;
    }

    public function findBy(array $criteria): void
    {
        throw new LogicException('Method is not supported by the storage.');
    }

    protected function doFind($id)
    {
        if (isset($this->identityMap[$id])) {
            return $this->identityMap[$id];
        }

        if (file_exists($this->storageDir . '/payum-model-' . $id)) {
            return $this->identityMap[$id] = unserialize(file_get_contents($this->storageDir . '/payum-model-' . $id));
        }
    }

    protected function doUpdateModel($model): void
    {
        $ro = new ReflectionObject($model);

        if (false == $ro->hasProperty($this->idProperty)) {
            $model->{$this->idProperty} = null;
        }

        $rp = new ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        $id = $rp->getValue($model);
        if (false == $id) {
            $rp->setValue($model, $id = uniqid());
        }

        $rp->setAccessible(false);

        $this->identityMap[$id] = $model;
        file_put_contents($this->storageDir . '/payum-model-' . $id, serialize($model));
    }

    protected function doDeleteModel($model): void
    {
        $rp = new ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        if ($id = $rp->getValue($model)) {
            unlink($this->storageDir . '/payum-model-' . $id);
            unset($this->identityMap[$id]);
        }
    }

    protected function doGetIdentity($model)
    {
        $rp = new ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        if (false == $id = $rp->getValue($model)) {
            throw new LogicException('The model must be persisted before usage of this method');
        }

        return new Identity($id, $model);
    }
}
