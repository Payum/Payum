<?php
namespace Payum\Core\Bridge\Doctrine\Storage;

use Doctrine\Common\Persistence\ObjectManager;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;

class DoctrineStorage extends AbstractStorage
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string                                     $modelClass
     */
    public function __construct(ObjectManager $objectManager, $modelClass)
    {
        parent::__construct($modelClass);

        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria)
    {
        return $this->objectManager->getRepository($this->modelClass)->findBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    protected function doFind($id)
    {
        return $this->objectManager->find($this->modelClass, $id);
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpdateModel($model)
    {
        $this->objectManager->persist($model);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    protected function doDeleteModel($model)
    {
        $this->objectManager->remove($model);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetIdentity($model)
    {
        $modelMetadata = $this->objectManager->getClassMetadata(get_class($model));
        $id = $modelMetadata->getIdentifierValues($model);
        if (count($id) > 1) {
            throw new \LogicException('Storage not support composite primary ids');
        }

        return new Identity(array_shift($id), $model);
    }
}
