<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Migrations;

use Composer\InstalledVersions;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Configuration as MigrationsConfiguration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\Migrations\Version\Direction;
use Doctrine\Migrations\Version\Version;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Payum\Core\Bridge\Doctrine\EventListener\StoreTokenDetailsAsJson;
use Payum\Core\Bridge\Doctrine\Types\DbalObjectType;
use Payum\Core\Model\Identity;
use Payum\Core\Tests\Functional\Bridge\Doctrine\Migrations\Fixtures\Version20261103000000;
use Payum\Core\Tests\Mocks\Entity\Payment;
use Payum\Core\Tests\Mocks\Entity\Token;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Runs on SQLite by default. Set PAYUM_DOCTRINE_TEST_URL (e.g. pdo_pgsql://user:pass@127.0.0.1:5432/payum)
 * to run it against another database; the database is wiped.
 */
final class TokenDetailsToJsonMigrationTest extends TestCase
{
    private Connection $connection;

    public static function setUpBeforeClass(): void
    {
        if (! class_exists(DependencyFactory::class)) {
            throw new SkippedTestError('doctrine/migrations is not installed.');
        }

        DbalObjectType::register();
    }

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(self::connectionParams());

        $schemaManager = $this->connection->createSchemaManager();
        foreach (['Token', 'doctrine_migration_versions'] as $table) {
            if ($schemaManager->tablesExist([$table])) {
                $schemaManager->dropTable($table);
            }
        }

        (new SchemaTool($this->entityManager(false)))->createSchema([
            $this->entityManager(false)->getClassMetadata(Token::class),
        ]);
    }

    public function testShouldConvertTokenDetailsToJsonAndBack(): void
    {
        $withDetails = $this->persistToken(new Identity(123, Payment::class));
        $withStringId = $this->persistToken(new Identity('a/ünïcode "id"', Payment::class));
        $withoutDetails = $this->persistToken(null);

        $this->migrate(Direction::UP);

        // MySQL reorders the keys of a JSON value.
        $this->assertEquals(
            [
                'class' => Payment::class,
                'id' => 123,
            ],
            json_decode($this->rawDetails($withDetails), true)
        );

        $entityManager = $this->entityManager(true);
        $this->assertEquals(new Identity(123, Payment::class), $entityManager->find(Token::class, $withDetails)->getDetails());
        $this->assertEquals(new Identity('a/ünïcode "id"', Payment::class), $entityManager->find(Token::class, $withStringId)->getDetails());
        $this->assertNull($this->connection->fetchOne('SELECT details FROM Token WHERE hash = ?', [$withoutDetails]));
        $this->assertSame([], $this->schemaChanges($entityManager), 'The migrated column must match the JSON mapping.');

        $this->migrate(Direction::DOWN);

        $entityManager = $this->entityManager(false);
        $this->assertEquals(new Identity(123, Payment::class), $entityManager->find(Token::class, $withDetails)->getDetails());
        $this->assertEquals(new Identity('a/ünïcode "id"', Payment::class), $entityManager->find(Token::class, $withStringId)->getDetails());
        $this->assertNull($this->connection->fetchOne('SELECT details FROM Token WHERE hash = ?', [$withoutDetails]));
        $this->assertSame([], $this->schemaChanges($entityManager), 'The reverted column must match the object mapping.');
    }

    public function testShouldLeaveTheDataAloneOnADryRunBack(): void
    {
        $hash = $this->persistToken(new Identity(123, Payment::class));
        $this->migrate(Direction::UP);
        $json = $this->rawDetails($hash);

        $this->migrate(Direction::DOWN, true);

        $this->assertSame($json, $this->rawDetails($hash));
        $this->assertEquals(new Identity(123, Payment::class), $this->entityManager(true)->find(Token::class, $hash)->getDetails());
    }

    public function testShouldWriteNewTokensAsJsonOnceMigrated(): void
    {
        $this->migrate(Direction::UP);

        $entityManager = $this->entityManager(true);
        $token = new Token();
        $token->setTargetUrl('aTargetUrl');
        $token->setGatewayName('aGateway');
        $token->setDetails(new Identity(5, Payment::class));
        $entityManager->persist($token);
        $entityManager->flush();

        $this->assertEquals([
            'class' => Payment::class,
            'id' => 5,
        ], json_decode($this->rawDetails($token->getHash()), true));
    }

    /**
     * @return array<string, mixed>
     */
    public static function connectionParams(): array
    {
        $url = getenv('PAYUM_DOCTRINE_TEST_URL');
        if (! $url) {
            return [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ];
        }

        // parse_url() rejects the underscore in "pdo_pgsql".
        $parts = parse_url(str_replace('_', '-', strstr($url, '://', true)) . strstr($url, '://'));

        return array_filter([
            'driver' => isset($parts['scheme']) ? str_replace('-', '_', $parts['scheme']) : null,
            'host' => $parts['host'] ?? null,
            'port' => $parts['port'] ?? null,
            'user' => isset($parts['user']) ? urldecode($parts['user']) : null,
            'password' => isset($parts['pass']) ? urldecode($parts['pass']) : null,
            'dbname' => isset($parts['path']) ? ltrim($parts['path'], '/') : null,
        ]);
    }

    private function persistToken(?Identity $details): string
    {
        $entityManager = $this->entityManager(false);

        $token = new Token();
        $token->setTargetUrl('aTargetUrl');
        $token->setGatewayName('aGateway');
        if ($details) {
            $token->setDetails($details);
        }

        $entityManager->persist($token);
        $entityManager->flush();

        return $token->getHash();
    }

    private function migrate(string $direction, bool $dryRun = false): void
    {
        $configuration = new MigrationsConfiguration();
        $configuration->addMigrationsDirectory('Payum\Core\Tests\Functional\Bridge\Doctrine\Migrations\Fixtures', __DIR__ . '/Fixtures');
        $configuration->setMetadataStorageConfiguration(new TableMetadataStorageConfiguration());

        $dependencyFactory = DependencyFactory::fromConnection(new ExistingConfiguration($configuration), new ExistingConnection($this->connection));
        $dependencyFactory->getMetadataStorage()->ensureInitialized();

        $plan = $dependencyFactory->getMigrationPlanCalculator()->getPlanForVersions([new Version(Version20261103000000::class)], $direction);
        $dependencyFactory->getMigrator()->migrate($plan, (new MigratorConfiguration())->setDryRun($dryRun));
    }

    private function rawDetails(string $hash): string
    {
        return (string) $this->connection->fetchOne('SELECT details FROM Token WHERE hash = ?', [$hash]);
    }

    /**
     * @return list<string>
     */
    private function schemaChanges(EntityManager $entityManager): array
    {
        $sql = (new SchemaTool($entityManager))->getUpdateSchemaSql([$entityManager->getClassMetadata(Token::class)]);

        return array_values(array_filter($sql, static fn (string $statement): bool => false === stripos($statement, 'doctrine_migration_versions')));
    }

    private function entityManager(bool $detailsAsJson): EntityManager
    {
        $root = dirname(__DIR__, 5);

        $driver = new MappingDriverChain();
        $driver->addDriver(new SimplifiedXmlDriver([
            $root . '/Bridge/Doctrine/Resources/mapping' => 'Payum\Core\Model',
        ]), 'Payum\Core\Model');
        $driver->addDriver(new AttributeDriver([$root . '/Tests/Mocks/Entity'], true), 'Payum\Core\Tests\Mocks\Entity');

        $config = new Configuration();
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Proxies');
        $config->setMetadataDriverImpl($driver);
        $config->setMetadataCache(new ArrayAdapter());
        $config->setQueryCache(new ArrayAdapter());
        if (\PHP_VERSION_ID >= 80400 && version_compare((string) InstalledVersions::getVersion('doctrine/orm'), '3.4', '>=')) {
            $config->enableNativeLazyObjects(true);
        }

        $eventManager = new EventManager();
        if ($detailsAsJson) {
            $eventManager->addEventListener(Events::loadClassMetadata, new StoreTokenDetailsAsJson());
        }

        return new EntityManager($this->connection, $config, $eventManager);
    }
}
