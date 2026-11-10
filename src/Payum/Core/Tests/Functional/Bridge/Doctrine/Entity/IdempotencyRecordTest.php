<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Entity;

use DateTimeImmutable;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Idempotency\StorageIdempotencyStore;
use Payum\Core\Model\IdempotencyRecord as BaseIdempotencyRecord;
use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Tests\Mocks\Entity\IdempotencyRecord;

final class IdempotencyRecordTest extends OrmTest
{
    public function testShouldStoreARecordAndReadItBack(): void
    {
        $store = new StorageIdempotencyStore(new DoctrineStorage($this->em, IdempotencyRecord::class));
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

        $this->em->clear();

        $found = $store->find($scope);

        $this->assertInstanceOf(IdempotencyRecord::class, $found);
        $this->assertNotSame($record, $found);
        $this->assertSame($record->getFingerprint(), $found->getFingerprint());
        $this->assertSame($record->getResult(), $found->getResult());
        $this->assertEquals($record->getExpiresAt(), $found->getExpiresAt());
    }

    public function testShouldStoreARecordTheMiddlewareBuiltAsTheMappedEntity(): void
    {
        $store = new StorageIdempotencyStore(new DoctrineStorage($this->em, IdempotencyRecord::class));

        $record = new BaseIdempotencyRecord();
        $record->setScope('scope_1');
        $record->setFingerprint('fp_1');
        $record->setResult([
            'type' => 'cancel',
        ]);
        $record->setExpiresAt(new DateTimeImmutable('2026-11-11 12:00:00'));
        $store->save($record);

        $this->em->clear();

        $this->assertInstanceOf(IdempotencyRecord::class, $store->find('scope_1'));
        $this->assertSame('fp_1', $store->find('scope_1')->getFingerprint());
    }

    public function testShouldReplaceARecordUnderTheSameScope(): void
    {
        $store = new StorageIdempotencyStore(new DoctrineStorage($this->em, IdempotencyRecord::class));

        foreach (['fp_1', 'fp_2'] as $fingerprint) {
            $record = new IdempotencyRecord();
            $record->setScope('scope_1');
            $record->setFingerprint($fingerprint);
            $record->setResult([]);
            $record->setExpiresAt(new DateTimeImmutable('2026-11-11 12:00:00'));
            $store->save($record);
            $this->em->clear();
        }

        $this->assertSame('fp_2', $store->find('scope_1')?->getFingerprint());
    }
}
