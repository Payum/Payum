<?php

declare(strict_types=1);

namespace Payum\Core\Idempotency;

use Payum\Core\Model\IdempotencyRecord;

/**
 * Where the first result of each keyed operation is kept.
 *
 * A store need not drop expired records: the middleware ignores a record past its expiry and saves the
 * next one over it.
 */
interface IdempotencyStoreInterface
{
    public function find(string $scope): ?IdempotencyRecord;

    /**
     * Replaces any record already saved under the same scope.
     */
    public function save(IdempotencyRecord $record): void;
}
