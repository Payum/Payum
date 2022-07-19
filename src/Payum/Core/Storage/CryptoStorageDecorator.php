<?php

namespace Payum\Core\Storage;

use LogicException;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;

/**
 * @template T of object
 * @implements StorageInterface<T>
 */
final class CryptoStorageDecorator implements StorageInterface
{
    /**
     * @var StorageInterface<T>
     */
    private StorageInterface $decoratedStorage;

    private CypherInterface $crypto;

    /**
     * @param StorageInterface<T> $decoratedStorage
     */
    public function __construct(StorageInterface $decoratedStorage, CypherInterface $crypto)
    {
        $this->decoratedStorage = $decoratedStorage;
        $this->crypto = $crypto;
    }

    public function create(): object
    {
        $model = $this->decoratedStorage->create();

        $this->assertCrypted($model);

        return $model;
    }

    public function support(object $model): bool
    {
        return $this->decoratedStorage->support($model);
    }

    public function update(object $model): object
    {
        $this->assertCrypted($model);

        $model->encrypt($this->crypto);

        $this->decoratedStorage->update($model);

        return $model;
    }

    public function delete(object $model): void
    {
        $this->decoratedStorage->delete($model);
    }

    public function find(IdentityInterface $id): ?object
    {
        $model = $this->decoratedStorage->find($id);

        if (! $model) {
            return null;
        }

        $this->assertCrypted($model);

        $model->decrypt($this->crypto);

        return $model;
    }

    /**
     * @return T[]
     */
    public function findBy(array $criteria): array
    {
        $models = $this->decoratedStorage->findBy($criteria);

        foreach ($models as $model) {
            $this->assertCrypted($model);

            $model->decrypt($this->crypto);
        }

        return $models;
    }

    public function identify(object $model): IdentityInterface
    {
        return $this->decoratedStorage->identify($model);
    }

    private function assertCrypted(object $model): void
    {
        if (! $model instanceof  CryptedInterface) {
            throw new LogicException(sprintf(
                'The model %s must implement %s interface. It is required for this decorator.',
                get_class($model),
                CryptedInterface::class
            ));
        }
    }
}
