<?php

declare(strict_types=1);

namespace Payum\Core\Idempotency;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\IdempotencyRecord;
use Payum\Core\Storage\StorageInterface;

/**
 * Keeps records in any Payum storage, such as Doctrine with the IdempotencyRecord mapping Payum ships.
 */
final class StorageIdempotencyStore implements IdempotencyStoreInterface
{
    /**
     * @param StorageInterface<object> $storage a storage of IdempotencyRecord models, identified by their scope
     */
    public function __construct(
        private readonly StorageInterface $storage
    ) {
    }

    public function find(string $scope): ?IdempotencyRecord
    {
        $record = $this->storage->find($scope);

        return $record instanceof IdempotencyRecord ? $record : null;
    }

    /**
     * The record is copied onto the storage's own model, which an application usually extends to map.
     *
     * @throws LogicException when the storage does not hold idempotency records
     */
    public function save(IdempotencyRecord $record): void
    {
        $model = $this->find($record->getScope()) ?? $this->storage->create();

        if (! $model instanceof IdempotencyRecord) {
            throw new LogicException(sprintf('%s needs a storage of %s, but this one creates %s.', self::class, IdempotencyRecord::class, $model::class));
        }

        if ($model !== $record) {
            $model->setScope($record->getScope());
            $model->setFingerprint($record->getFingerprint());
            $model->setResult($record->getResult());
            $model->setExpiresAt($record->getExpiresAt());
        }

        $this->storage->update($model);
    }
}
