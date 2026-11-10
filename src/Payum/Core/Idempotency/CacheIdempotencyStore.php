<?php

declare(strict_types=1);

namespace Payum\Core\Idempotency;

use DateTimeImmutable;
use Payum\Core\Model\IdempotencyRecord;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Keeps records in a PSR-6 cache pool, which drops each one when it expires.
 *
 * Use a pool that is shared between your servers and is not cleared on deploy, or a repeated key goes
 * unrecognised.
 */
final class CacheIdempotencyStore implements IdempotencyStoreInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface $pool,
        private readonly string $prefix = 'payum_idempotency_',
    ) {
    }

    public function find(string $scope): ?IdempotencyRecord
    {
        $item = $this->pool->getItem($this->prefix . $scope);

        if (! $item->isHit() || ! is_array($data = $item->get())) {
            return null;
        }

        $record = new IdempotencyRecord();
        $record->setScope($scope);
        $record->setFingerprint($data['fingerprint']);
        $record->setResult($data['result']);
        $record->setExpiresAt((new DateTimeImmutable())->setTimestamp($data['expiresAt']));

        return $record;
    }

    public function save(IdempotencyRecord $record): void
    {
        $item = $this->pool->getItem($this->prefix . $record->getScope());
        $item->set([
            'fingerprint' => $record->getFingerprint(),
            'result' => $record->getResult(),
            'expiresAt' => $record->getExpiresAt()->getTimestamp(),
        ]);
        $item->expiresAt($record->getExpiresAt());

        $this->pool->save($item);
    }
}
