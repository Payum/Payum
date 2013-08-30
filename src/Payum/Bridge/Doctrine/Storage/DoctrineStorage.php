<?php
namespace Payum\Bridge\Doctrine\Storage;

use Doctrine\Common\Persistence\ObjectManager;
use Payum\Storage\AbstractStorage;
use Payum\Storage\Identificator;

class DoctrineStorage extends AbstractStorage
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string $modelClass
     */
    public function __construct(ObjectManager $objectManager, $modelClass)
    {
        parent::__construct($modelClass);

        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritDoc}
     */
    public function findModelById($id)
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
    protected function doGetIdentificator($model)
    {
        $modelMetadata = $this->objectManager->getClassMetadata(get_class($model));
        $id = $modelMetadata->getIdentifierValues($model);
        if (count($id) > 1) {
            throw new \LogicException('Storage not support composite primary ids');
        }

        return new Identificator(array_shift($id), $model);
    }
}
