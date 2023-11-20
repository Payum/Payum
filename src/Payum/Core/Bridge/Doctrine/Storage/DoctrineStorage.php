<?php

namespace Payum\Core\Bridge\Doctrine\Storage;

use Doctrine\Persistence\ObjectManager;
use LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\IdentityInterface;

/**
 * @template T of object
 * @extends AbstractStorage<T>
 */
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

    /**
     * @return T[]
     */
    public function findBy(array $criteria): array
    {
        return $this->objectManager->getRepository($this->modelClass)->findBy($criteria);
    }

    protected function doFind(mixed $id): ?object
    {
        return $this->objectManager->find($this->modelClass, $id);
    }

    protected function doUpdateModel(object $model): object
    {
        $this->objectManager->persist($model);
        $this->objectManager->flush();

        return $model;
    }

    protected function doDeleteModel(object $model): void
    {
        $this->objectManager->remove($model);
        $this->objectManager->flush();
    }

    protected function doGetIdentity(object $model): IdentityInterface
    {
        $modelMetadata = $this->objectManager->getClassMetadata($model::class);
        $id = $modelMetadata->getIdentifierValues($model);
        if (count($id) > 1) {
            throw new LogicException('Storage not support composite primary ids');
        }

        return new Identity(array_shift($id), $model);
    }
}
