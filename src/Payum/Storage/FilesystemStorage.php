<?php
namespace Payum\Storage;

use Payum\Exception\InvalidArgumentException;
use Payum\Exception\LogicException;
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
    public function supportModel($model)
    {
        return 
            $model instanceof $this->modelClass || 
            (is_string($model) && $model === $this->modelClass)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function updateModel($model)
    {
        if (false == $this->supportModel($model)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s',
                $this->modelClass
            ));
        }

        $rp = new \ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);
        
        $id = $rp->getValue($model);
        if (false == $id) {
            $rp->setValue($model, $id = uniqid());
        }
        
        $rp->setAccessible(false);

        file_put_contents($this->storageDir.'/payum-model-'.$id, serialize($model));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteModel($model)
    {
        if (false == $this->supportModel($model)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s',
                $this->modelClass
            ));
        }

        $rp = new \ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        
        if ($id = $rp->getValue($model)) {
            unlink($this->storageDir.'/payum-model-'.$id);
        }
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

    /**
     * {@inheritdoc}
     */
    function getIdentificator($model)
    {
        if (false == $this->supportModel($model)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s',
                $this->modelClass
            ));
        }

        $rp = new \ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        if (false == $id = $rp->getValue($model)) {
            throw new LogicException('The model must be persisted before usage of this method');
        }
        
        return new Identificator($id, $model);
    }
}