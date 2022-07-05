<?php

namespace Payum\Core\Bridge\Doctrine\Storage;

use Doctrine\Persistence\ObjectManager;
use LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;

class DoctrineStorage extends AbstractStorage
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param string                              $modelClass
     */
    public function __construct(ObjectManager $objectManager, $modelClass)
    {
        parent::__construct($modelClass);

        $this->objectManager = $objectManager;
    }

    public function findBy(array $criteria)
    {
        return $this->objectManager->getRepository($this->modelClass)->findBy($criteria);
    }

    protected function doFind($id)
    {
        return $this->objectManager->find($this->modelClass, $id);
    }

    protected function doUpdateModel($model)
    {
        $this->objectManager->persist($model);
        $this->objectManager->flush();
    }

    protected function doDeleteModel($model)
    {
        $this->objectManager->remove($model);
        $this->objectManager->flush();
    }

    protected function doGetIdentity($model)
    {
        $modelMetadata = $this->objectManager->getClassMetadata(get_class($model));
        $id = $modelMetadata->getIdentifierValues($model);
        if (count($id) > 1) {
            throw new LogicException('Storage not support composite primary ids');
        }

        return new Identity(array_shift($id), $model);
    }
}
