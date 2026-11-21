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
        $existing = $this->find($namespace, $reference);
        $model = $existing ?? $this->storage->create();

        if (! $model instanceof ReferenceRecord) {
            throw new LogicException(sprintf('%s needs a storage of %s, but this one creates %s.', self::class, ReferenceRecord::class, $model::class));
        }

        $held = $existing ? self::heldBy($existing) : null;

        $model->populate($namespace, $reference, $gatewayName, $subject);

        if ($held === self::heldBy($model)) {
            return;
        }

        $this->storage->update($model);
    }

    public function find(string $namespace, string $reference): ?ReferenceRecord
    {
        $record = $this->storage->find(ReferenceRecord::keyFor($namespace, $reference));

        return $record instanceof ReferenceRecord ? $record : null;
    }

    /**
     * @return array{string, string, string}
     */
    private static function heldBy(ReferenceRecord $record): array
    {
        return [$record->getGatewayName(), $record->getSubjectClass(), $record->getSubjectId()];
    }
}
