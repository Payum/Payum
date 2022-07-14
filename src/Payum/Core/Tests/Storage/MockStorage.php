<?php
declare(strict_types=1);

namespace Payum\Core\Tests\Storage;

use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;
use stdClass;

/**
 * @template T of object
 * @extends AbstractStorage<T>
 */
final class MockStorage extends AbstractStorage
{
    protected function doUpdateModel(object $model): object
    {
        return $model;
    }

    protected function doDeleteModel(object $model): void {}

    protected function doGetIdentity(object $model): IdentityInterface
    {
        return new Identity('foo', new ($this->modelClass));
    }

    protected function doFind(mixed $id): ?object
    {
        return null;
    }

    /**
     * @param array{string, mixed} $criteria
     *
     * @return list<T>
     */
    public function findBy(array $criteria): array
    {
        return [];
    }
}
