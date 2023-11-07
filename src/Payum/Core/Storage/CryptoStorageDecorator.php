<?php

namespace Payum\Core\Storage;

use LogicException;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;

final class CryptoStorageDecorator implements StorageInterface
{
    private StorageInterface $decoratedStorage;

    private CypherInterface $crypto;

    public function __construct(StorageInterface $decoratedStorage, CypherInterface $crypto)
    {
        $this->decoratedStorage = $decoratedStorage;
        $this->crypto = $crypto;
    }

    public function create()
    {
        $model = $this->decoratedStorage->create();

        $this->assertCrypted($model);

        return $model;
    }

    public function support($model)
    {
        return $this->decoratedStorage->support($model);
    }

    public function update($model): void
    {
        $this->assertCrypted($model);

        $model->encrypt($this->crypto);

        $this->decoratedStorage->update($model);
    }

    public function delete($model): void
    {
        $this->decoratedStorage->delete($model);
    }

    public function find($id)
    {
        $model = $this->decoratedStorage->find($id);

        $this->assertCrypted($model);

        $model->decrypt($this->crypto);

        return $model;
    }

    public function findBy(array $criteria)
    {
        $models = $this->decoratedStorage->findBy($criteria);

        foreach ($models as $model) {
            $this->assertCrypted($model);

            $model->decrypt($this->crypto);
        }

        return $models;
    }

    public function identify($model)
    {
        return $this->decoratedStorage->identify($model);
    }

    /**
     * @param object $model
     */
    private function assertCrypted($model): void
    {
        if (false == $model instanceof CryptedInterface) {
            throw new LogicException(sprintf(
                'The model %s must implement %s interface. It is required for this decorator.',
                $model::class,
                CryptedInterface::class
            ));
        }
    }
}
