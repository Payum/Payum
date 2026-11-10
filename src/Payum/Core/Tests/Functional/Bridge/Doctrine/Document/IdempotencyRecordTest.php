<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Document;

use DateTimeImmutable;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Idempotency\StorageIdempotencyStore;
use Payum\Core\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Tests\Mocks\Document\IdempotencyRecord;

final class IdempotencyRecordTest extends MongoTest
{
    public function testShouldStoreARecordAndReadItBack(): void
    {
        $store = new StorageIdempotencyStore(new DoctrineStorage($this->dm, IdempotencyRecord::class));
        $scope = hash('sha256', 'acme|order-1');

        $record = new IdempotencyRecord();
        $record->setScope($scope);
        $record->setFingerprint(hash('sha256', 'capture'));
        $record->setResult([
            'type' => 'capture',
            'status' => 'captured',
            'transactionId' => 'ch_1',
        ]);
        $record->setExpiresAt(new DateTimeImmutable('2026-11-11 12:00:00'));
        $store->save($record);

        $this->dm->clear();

        $found = $store->find($scope);

        $this->assertInstanceOf(IdempotencyRecord::class, $found);
        $this->assertNotSame($record, $found);
        $this->assertSame($record->getFingerprint(), $found->getFingerprint());
        $this->assertSame($record->getResult(), $found->getResult());
        $this->assertEquals($record->getExpiresAt(), $found->getExpiresAt());
    }

    public function testShouldReplaceARecordUnderTheSameScope(): void
    {
        $store = new StorageIdempotencyStore(new DoctrineStorage($this->dm, IdempotencyRecord::class));

        foreach (['fp_1', 'fp_2'] as $fingerprint) {
            $record = new IdempotencyRecord();
            $record->setScope('scope_1');
            $record->setFingerprint($fingerprint);
            $record->setResult([]);
            $record->setExpiresAt(new DateTimeImmutable('2026-11-11 12:00:00'));
            $store->save($record);
            $this->dm->clear();
        }

        $this->assertSame('fp_2', $store->find('scope_1')?->getFingerprint());
    }
}
