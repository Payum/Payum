<?php

namespace Payum\Core\Storage;

use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;

final class CryptoStorageDecorator implements StorageInterface
{
    /**
     * @var StorageInterface
     */
    private $realStorage;

    /**
     * @var CypherInterface
     */
    private $crypto;

    /**
     * @param StorageInterface $realStorage
     * @param CypherInterface $crypto
     */
    public function __construct(StorageInterface $realStorage, CypherInterface $crypto)
    {
        $this->realStorage = $realStorage;
        $this->crypto = $crypto;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->realStorage->create();
    }

    /**
     * {@inheritdoc}
     */
    public function support($model)
    {
        return $this->realStorage->support($model);
    }

    /**
     * {@inheritdoc}
     */
    public function update($model)
    {
        if ($model instanceof CryptedInterface) {
            $model->encrypt($this->crypto);
        }

        $this->realStorage->update($model);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($model)
    {
        $this->realStorage->delete($model);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $model = $this->realStorage->find($id);

        if ($model instanceof CryptedInterface) {
            $model->decrypt($this->crypto);
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria)
    {
        $models = $this->realStorage->findBy($criteria);

        foreach ($models as $model) {
            if ($model instanceof CryptedInterface) {
                $model->decrypt($this->crypto);
            }
        }

        return $models;
    }

    /**
     * {@inheritdoc}
     */
    public function identify($model)
    {
        return $this->realStorage->identify($model);
    }
}
