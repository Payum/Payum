<?php

declare(strict_types=1);

namespace Payum\Core\Reference;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\ReferenceRecord;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;

/**
 * Keeps references in any Payum storage, such as Doctrine with the ReferenceRecord mapping Payum ships.
 */
final class StorageReferenceIndex implements ReferenceIndexInterface
{
    /**
     * @param StorageInterface<object> $storage a storage of ReferenceRecord models, identified by their key
     */
    public function __construct(
        private readonly StorageInterface $storage
    ) {
    }

    /**
     * @throws LogicException when the storage does not hold reference records
     */
    public function record(string $namespace, string $reference, string $gatewayName, IdentityInterface $subject): void
    {
        $key = self::key($namespace, $reference);
        $model = $this->find($namespace, $reference) ?? $this->storage->create();

        if (! $model instanceof ReferenceRecord) {
            throw new LogicException(sprintf('%s needs a storage of %s, but this one creates %s.', self::class, ReferenceRecord::class, $model::class));
        }

        $model->setKey($key);
        $model->setNamespace($namespace);
        $model->setReference($reference);
        $model->setGatewayName($gatewayName);
        $model->setSubjectClass($subject->getClass());
        $model->setSubjectId((string) $subject->getId());

        $this->storage->update($model);
    }

    public function find(string $namespace, string $reference): ?ReferenceRecord
    {
        $record = $this->storage->find(self::key($namespace, $reference));

        return $record instanceof ReferenceRecord ? $record : null;
    }

    private static function key(string $namespace, string $reference): string
    {
        return hash('sha256', $namespace . "\0" . $reference);
    }
}
