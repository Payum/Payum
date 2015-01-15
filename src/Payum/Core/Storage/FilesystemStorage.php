<?php
namespace Payum\Core\Storage;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;

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

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria)
    {
        throw new LogicException('Method is not supported by the storage.');
    }

    /**
     * {@inheritDoc}
     */
    protected function doFind($id)
    {
        if (isset($this->identityMap[$id])) {
            return $this->identityMap[$id];
        }

        if (file_exists($this->storageDir.'/payum-model-'.$id)) {
            return $this->identityMap[$id] = unserialize(file_get_contents($this->storageDir.'/payum-model-'.$id));
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

        $this->identityMap[$id] = $model;
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
            unset($this->identityMap[$id]);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetIdentity($model)
    {
        $rp = new \ReflectionProperty($model, $this->idProperty);
        $rp->setAccessible(true);

        if (false == $id = $rp->getValue($model)) {
            throw new LogicException('The model must be persisted before usage of this method');
        }

        return new Identity($id, $model);
    }
}
