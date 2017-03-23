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
        $model = $this->decoratedStorage->create();

        $this->assertCrypted($model);

        return $model;
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
        $this->assertCrypted($model);

        $model->encrypt($this->crypto);

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

        $this->assertCrypted($model);

        $model->decrypt($this->crypto);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria)
    {
        $models = $this->decoratedStorage->findBy($criteria);

        foreach ($models as $model) {
            $this->assertCrypted($model);

            $model->decrypt($this->crypto);
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

    /**
     * @param object $model
     */
    private function assertCrypted($model)
    {
        if (false == $model instanceof  CryptedInterface) {
            throw new \LogicException(sprintf(
                'The model %s must implement %s interface. It is required for this decorator.',
                get_class($model),
                CryptedInterface::class
            ));
        }
    }
}
