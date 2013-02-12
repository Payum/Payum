<?php
namespace Payum\Storage;

use Payum\Exception\InvalidArgumentException;
use Payum\Storage\StorageInterface;

class FilesystemStorage implements StorageInterface
{
    protected $storageDir;

    protected $modelClass;

    protected $idProperty;

    public function __construct($storageDir, $modelClass, $idProperty)
    {
        $this->storageDir = $storageDir;
        $this->modelClass = $modelClass;
        $this->idProperty = $idProperty;
    }

    /**
     * {@inheritdoc}
     */
    public function createModel()
    {
        return new $this->modelClass;
    }

    /**
     * {@inheritdoc}
     */
    public function updateModel($model)
    {
        if (false == $model instanceof $this->modelClass) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s',
                $this->modelClass
            ));
        }

        $rp = new \ReflectionProperty($model, $this->idProperty);
        
        $rp->setAccessible(true);
        $id = $rp->getValue($model);
        $rp->setAccessible(false);
        if (false == $id) {
            $id = uniqid();

            $rp->setAccessible(true);
            $rp->setValue($model, $id);
            $rp->setAccessible(false);
        }

        file_put_contents($this->storageDir.'/payum-model-'.$id, serialize($model));
    }

    /**
     * {@inheritdoc}
     */
    public function findModelById($id)
    {
        if (file_exists($this->storageDir.'/payum-model-'.$id)) {
            return unserialize(file_get_contents($this->storageDir.'/payum-model-'.$id));
        }
    }
}