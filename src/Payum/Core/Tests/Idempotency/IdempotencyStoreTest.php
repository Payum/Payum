<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Idempotency;

use DateTimeImmutable;
use Payum\Core\Idempotency\IdempotencyStoreInterface;
use Payum\Core\Idempotency\InMemoryIdempotencyStore;
use Payum\Core\Idempotency\StorageIdempotencyStore;
use Payum\Core\Model\IdempotencyRecord;
use Payum\Core\Storage\FilesystemStorage;
use PHPUnit\Framework\TestCase;

final class IdempotencyStoreTest extends TestCase
{
    /**
     * @return iterable<string, array{callable(): IdempotencyStoreInterface}>
     */
    public static function provideStores(): iterable
    {
        yield 'in memory' => [
            static fn (): IdempotencyStoreInterface => new InMemoryIdempotencyStore(),
        ];
        yield 'storage' => [
            static fn (): IdempotencyStoreInterface => new StorageIdempotencyStore(
                new FilesystemStorage(sys_get_temp_dir(), IdempotencyRecord::class, 'scope'),
            ),
        ];
    }

    /**
     * @dataProvider provideStores
     *
     * @param callable(): IdempotencyStoreInterface $store
     */
    public function testShouldFindWhatItSaved(callable $store): void
    {
        $store = $store();
        $scope = hash('sha256', uniqid('', true));

        $this->assertNull($store->find($scope));

        $store->save($this->record($scope, 'fp_1', '2026-11-11 12:00:00'));
        $found = $store->find($scope);

        $this->assertInstanceOf(IdempotencyRecord::class, $found);
        $this->assertSame('fp_1', $found->getFingerprint());
        $this->assertSame([
            'type' => 'capture',
        ], $found->getResult());
        $this->assertEquals(new DateTimeImmutable('2026-11-11 12:00:00'), $found->getExpiresAt());
    }

    /**
     * @dataProvider provideStores
     *
     * @param callable(): IdempotencyStoreInterface $store
     */
    public function testShouldReplaceAnExpiredRecordUnderTheSameScope(callable $store): void
    {
        $store = $store();
        $scope = hash('sha256', uniqid('', true));

        $store->save($this->record($scope, 'fp_1', '2026-11-11 12:00:00'));
        $store->save($this->record($scope, 'fp_2', '2026-11-12 12:00:00'));

        $this->assertSame('fp_2', $store->find($scope)?->getFingerprint());
    }

    private function record(string $scope, string $fingerprint, string $expiresAt): IdempotencyRecord
    {
        $record = new IdempotencyRecord();
        $record->setScope($scope);
        $record->setFingerprint($fingerprint);
        $record->setResult([
            'type' => 'capture',
        ]);
        $record->setExpiresAt(new DateTimeImmutable($expiresAt));

        return $record;
    }
}
