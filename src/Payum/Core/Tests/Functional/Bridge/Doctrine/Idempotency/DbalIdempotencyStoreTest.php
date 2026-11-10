<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Idempotency;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Payum\Core\Bridge\Doctrine\Idempotency\DbalIdempotencyStore;
use Payum\Core\Model\IdempotencyRecord;
use PHPUnit\Framework\TestCase;

final class DbalIdempotencyStoreTest extends TestCase
{
    public static function createStore(?Connection $connection = null, string $table = DbalIdempotencyStore::DEFAULT_TABLE): DbalIdempotencyStore
    {
        $connection ??= DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $store = new DbalIdempotencyStore($connection, $table);

        $schema = new Schema();
        $store->configureSchema($schema);
        $connection->createSchemaManager()->createTable($schema->getTable($table));

        return $store;
    }

    public function testShouldAddItsTableToASchemaOnce(): void
    {
        $schema = new Schema();
        $store = new DbalIdempotencyStore(DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]));

        $store->configureSchema($schema);
        $store->configureSchema($schema);

        $this->assertSame(['scope'], $schema->getTable('payum_idempotency_record')->getPrimaryKey()?->getColumns());
    }

    public function testShouldUseTheTableItIsGiven(): void
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $store = self::createStore($connection, 'acme_keys');

        $store->save($this->record('scope_1', '2099-01-01 00:00:00'));

        $this->assertSame(1, (int) $connection->fetchOne('SELECT COUNT(*) FROM acme_keys'));
    }

    public function testShouldDeleteOnlyExpiredRecords(): void
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $store = self::createStore($connection);

        $store->save($this->record('expired', '2026-11-09 12:00:00'));
        $store->save($this->record('current', '2026-11-11 12:00:00'));

        $this->assertSame(1, $store->deleteExpired(new DateTimeImmutable('2026-11-10 12:00:00')));
        $this->assertNull($store->find('expired'));
        $this->assertNotNull($store->find('current'));
    }

    public function testShouldStoreTheResultAsJson(): void
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $store = self::createStore($connection);

        $store->save($this->record('scope_1', '2099-01-01 00:00:00'));

        $this->assertSame([
            'type' => 'capture',
            'transactionId' => 'ch_1',
        ], json_decode((string) $connection->fetchOne('SELECT result FROM payum_idempotency_record'), true));
    }

    private function record(string $scope, string $expiresAt): IdempotencyRecord
    {
        $record = new IdempotencyRecord();
        $record->setScope($scope);
        $record->setFingerprint('fp');
        $record->setResult([
            'type' => 'capture',
            'transactionId' => 'ch_1',
        ]);
        $record->setExpiresAt(new DateTimeImmutable($expiresAt));

        return $record;
    }
}
