<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Idempotency;

use DateTimeImmutable;
use Payum\Core\Idempotency\CacheIdempotencyStore;
use Payum\Core\Model\IdempotencyRecord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CacheIdempotencyStoreTest extends TestCase
{
    public function testShouldLetTheCacheExpireARecordWhenTheRecordExpires(): void
    {
        $store = new CacheIdempotencyStore(new ArrayAdapter());

        $store->save($this->record('scope_1', new DateTimeImmutable('-1 second')));

        $this->assertNull($store->find('scope_1'));
    }

    public function testShouldKeepOnlyPlainValuesInTheCache(): void
    {
        $pool = new ArrayAdapter(storeSerialized: false);
        $store = new CacheIdempotencyStore($pool);

        $store->save($this->record('scope_1', new DateTimeImmutable('+1 day')));

        $this->assertIsArray($pool->getItem('payum_idempotency_scope_1')->get());
    }

    private function record(string $scope, DateTimeImmutable $expiresAt): IdempotencyRecord
    {
        $record = new IdempotencyRecord();
        $record->setScope($scope);
        $record->setFingerprint('fp');
        $record->setResult([
            'type' => 'cancel',
        ]);
        $record->setExpiresAt($expiresAt);

        return $record;
    }
}
