<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Migrations;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Configuration\Configuration as MigrationsConfiguration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\Migrations\Version\Direction;
use Doctrine\Migrations\Version\Version;
use Payum\Core\Bridge\Doctrine\Idempotency\DbalIdempotencyStore;
use Payum\Core\Model\IdempotencyRecord;
use Payum\Core\Tests\Functional\Bridge\Doctrine\Migrations\Fixtures\Version20261110000000;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;

/**
 * Runs on SQLite by default. Set PAYUM_DOCTRINE_TEST_URL (e.g. pdo_pgsql://user:pass@127.0.0.1:5432/payum)
 * to run it against another database; the table is dropped.
 */
final class CreateIdempotencyTableMigrationTest extends TestCase
{
    private Connection $connection;

    public static function setUpBeforeClass(): void
    {
        if (! class_exists(DependencyFactory::class)) {
            throw new SkippedTestError('doctrine/migrations is not installed.');
        }
    }

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(TokenDetailsToJsonMigrationTest::connectionParams());

        $schemaManager = $this->connection->createSchemaManager();
        foreach ([DbalIdempotencyStore::DEFAULT_TABLE, 'doctrine_migration_versions'] as $table) {
            if ($schemaManager->tablesExist([$table])) {
                $schemaManager->dropTable($table);
            }
        }
    }

    public function testShouldCreateATableTheStoreCanUse(): void
    {
        $this->migrate(Direction::UP);

        $store = new DbalIdempotencyStore($this->connection);
        $record = new IdempotencyRecord();
        $record->setScope(hash('sha256', 'acme' . "\0" . 'order-1'));
        $record->setFingerprint(hash('sha256', 'capture'));
        $record->setResult([
            'type' => 'capture',
            'status' => 'captured',
            'transactionId' => 'ch_1',
            'note' => 'a/ünïcode "value"',
        ]);
        $record->setExpiresAt(new DateTimeImmutable('2099-01-01 12:00:00'));
        $store->save($record);

        $record->setFingerprint(hash('sha256', 'replaced'));
        $store->save($record);
        // MySQL reports no affected rows for an update that changes nothing.
        $store->save($record);

        $found = $store->find($record->getScope());

        $this->assertNotNull($found);
        $this->assertSame($record->getFingerprint(), $found->getFingerprint());
        $this->assertSame($record->getResult(), $found->getResult());
        $this->assertEquals($record->getExpiresAt(), $found->getExpiresAt());
    }

    public function testShouldMatchTheSchemaTheStoreDescribes(): void
    {
        $this->migrate(Direction::UP);

        $expected = new Schema();
        (new DbalIdempotencyStore($this->connection))->configureSchema($expected);

        $schemaManager = $this->connection->createSchemaManager();
        $diff = $schemaManager->createComparator()->compareTables(
            $schemaManager->introspectTable(DbalIdempotencyStore::DEFAULT_TABLE),
            $expected->getTable(DbalIdempotencyStore::DEFAULT_TABLE),
        );

        $this->assertSame([], $this->connection->getDatabasePlatform()->getAlterTableSQL($diff));
    }

    public function testShouldDropTheTableOnTheWayBack(): void
    {
        $this->migrate(Direction::UP);
        $this->migrate(Direction::DOWN);

        $this->assertFalse($this->connection->createSchemaManager()->tablesExist([DbalIdempotencyStore::DEFAULT_TABLE]));
    }

    private function migrate(string $direction): void
    {
        $configuration = new MigrationsConfiguration();
        $configuration->addMigrationsDirectory('Payum\Core\Tests\Functional\Bridge\Doctrine\Migrations\Fixtures', __DIR__ . '/Fixtures');
        $configuration->setMetadataStorageConfiguration(new TableMetadataStorageConfiguration());

        $dependencyFactory = DependencyFactory::fromConnection(new ExistingConfiguration($configuration), new ExistingConnection($this->connection));
        $dependencyFactory->getMetadataStorage()->ensureInitialized();

        $plan = $dependencyFactory->getMigrationPlanCalculator()->getPlanForVersions([new Version(Version20261110000000::class)], $direction);
        $dependencyFactory->getMigrator()->migrate($plan, new MigratorConfiguration());
    }
}
