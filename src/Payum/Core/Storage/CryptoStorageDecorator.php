<?php

namespace Payum\Core\Storage;

use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;

final class CryptoStorageDecorator implements StorageInterface
{
    /**
     * @var StorageInterface
     */
    private $decoratedStorage;

    /**
     * @var CypherInterface
     */
    private $crypto;

    /**
     * @param StorageInterface $decoratedStorage
     * @param CypherInterface $crypto
     */
    public function __construct(StorageInterface $decoratedStorage, CypherInterface $crypto)
    {
        $this->decoratedStorage = $decoratedStorage;
        $this->crypto = $crypto;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->decoratedStorage->create();
    }

    /**
     * {@inheritdoc}
     */
    public function support($model)
    {
        return $this->decoratedStorage->support($model);
    }

    /**
     * {@inheritdoc}
     */
    public function update($model)
    {
        if ($model instanceof CryptedInterface) {
            $model->encrypt($this->crypto);
        }

        $this->decoratedStorage->update($model);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($model)
    {
        $this->decoratedStorage->delete($model);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $model = $this->decoratedStorage->find($id);

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
        $models = $this->decoratedStorage->findBy($criteria);

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
        return $this->decoratedStorage->identify($model);
    }
}
