<?php
namespace Payum\Core\Bridge\Doctrine\Storage;

use Doctrine\Persistence\ObjectManager;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\IdentityInterface;

class DoctrineStorage extends AbstractStorage
{
    public function __construct(protected ObjectManager $objectManager, string $modelClass)
    {
        parent::__construct($modelClass);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria): array
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
    protected function doUpdateModel($model): void
    {
        $this->objectManager->persist($model);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    protected function doDeleteModel($model): void
    {
        $this->objectManager->remove($model);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetIdentity($model): IdentityInterface
    {
        $modelMetadata = $this->objectManager->getClassMetadata(get_class($model));
        $id = $modelMetadata->getIdentifierValues($model);
        if (count($id) > 1) {
            throw new \LogicException('Storage not support composite primary ids');
        }

        return new Identity(array_shift($id), $model);
    }
}
