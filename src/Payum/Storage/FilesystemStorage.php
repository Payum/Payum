<?php
namespace Payum\Storage;

use Payum\Exception\LogicException;
use Payum\Model\Identificator as ModelIdentificator;

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

    /**
     * {@inheritDoc}
     */
    public function findModelById($id)
    {
        if (file_exists($this->storageDir.'/payum-model-'.$id)) {
            return unserialize(file_get_contents($this->storageDir.'/payum-model-'.$id));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpdateModel($model)
    {
        $ro = new \ReflectionObject($model);

        if (false == $ro->hasProperty($this->idProperty)) {
            $model->{$this->idProperty} = null;
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
     * {@inheritDoc}
     */
    protected function doDeleteModel($model)
    {
        $rp = new \ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        if ($id = $rp->getValue($model)) {
            unlink($this->storageDir.'/payum-model-'.$id);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetIdentificator($model)
    {
        $rp = new \ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        if (false == $id = $rp->getValue($model)) {
            throw new LogicException('The model must be persisted before usage of this method');
        }

        return new ModelIdentificator($id, $model);
    }
}