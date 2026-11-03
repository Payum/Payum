<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Doctrine\Migrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Payum\Core\Bridge\Doctrine\Types\DbalObjectType;
use Payum\Core\Bridge\Doctrine\Types\IdentityType;
use Payum\Core\Storage\IdentityInterface;
use UnexpectedValueException;

/**
 * Converts the `details` column of Payum token tables from serialized PHP (`object`) to JSON (`payum_identity`).
 *
 * Extend it in your application, list your token tables in tokenTables(), then register
 * Payum\Core\Bridge\Doctrine\EventListener\StoreTokenDetailsAsJson.
 */
abstract class AbstractTokenDetailsToJsonMigration extends AbstractMigration
{
    private const ID_COLUMN = 'hash';

    private const DETAILS_COLUMN = 'details';

    public function getDescription(): string
    {
        return 'Store Payum token details as JSON instead of serialized PHP';
    }

    /**
     * MySQL commits implicitly on every ALTER TABLE, which leaves no transaction for the migrator to commit.
     */
    public function isTransactional(): bool
    {
        return ! $this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform;
    }

    public function up(Schema $schema): void
    {
        IdentityType::register();

        foreach ($this->tokenTables() as $table) {
            foreach ($this->fetchDetails($table) as $hash => $details) {
                $identity = $this->unserialize($table, $hash, $details);
                $this->addSql($this->updateDetailsSql($table), [$identity ? IdentityType::toJson($identity) : null, $hash]);
            }

            // The data is now valid JSON, but PostgreSQL still refuses to cast text to json without USING.
            $platform = $this->connection->getDatabasePlatform();
            if ($platform instanceof PostgreSQLPlatform) {
                $this->addSql(sprintf(
                    'ALTER TABLE %s ALTER COLUMN %2$s TYPE %3$s USING %2$s::%3$s',
                    $table,
                    self::DETAILS_COLUMN,
                    $platform->getJsonTypeDeclarationSQL([]),
                ));
            }

            $schema->getTable($table)->getColumn(self::DETAILS_COLUMN)->setType(Type::getType(IdentityType::NAME));
        }
    }

    public function down(Schema $schema): void
    {
        DbalObjectType::register();

        foreach ($this->tokenTables() as $table) {
            $column = $schema->getTable($table)->getColumn(self::DETAILS_COLUMN);
            $column->setType(Type::getType(DbalObjectType::NAME));

            // MariaDB gives a JSON column a binary collation, which would otherwise be carried over to the text column.
            $column->setPlatformOptions(array_diff_key($column->getPlatformOptions(), [
                'charset' => true,
                'collation' => true,
            ]));
        }
    }

    /**
     * Serialized PHP is not valid JSON, so going back the column type changes first and the data after.
     */
    public function postDown(Schema $schema): void
    {
        foreach ($this->tokenTables() as $table) {
            foreach ($this->fetchDetails($table) as $hash => $details) {
                $this->connection->executeStatement($this->updateDetailsSql($table), [serialize(IdentityType::fromJson($details)), $hash]);
            }
        }
    }

    /**
     * The tables of every entity in your application that extends Payum\Core\Model\Token.
     *
     * @return list<string>
     */
    abstract protected function tokenTables(): array;

    /**
     * @return array<string, string>
     */
    private function fetchDetails(string $table): array
    {
        $details = [];
        $rows = $this->connection->fetchAllNumeric(sprintf(
            'SELECT %s, %s FROM %s WHERE %2$s IS NOT NULL',
            self::ID_COLUMN,
            self::DETAILS_COLUMN,
            $table,
        ));

        foreach ($rows as [$hash, $value]) {
            $details[(string) $hash] = is_resource($value) ? (string) stream_get_contents($value) : (string) $value;
        }

        return $details;
    }

    private function updateDetailsSql(string $table): string
    {
        return sprintf(
            'UPDATE %s SET %s = ? WHERE %s = ?',
            $table,
            self::DETAILS_COLUMN,
            self::ID_COLUMN,
        );
    }

    private function unserialize(string $table, string $hash, string $details): ?IdentityInterface
    {
        $identity = @unserialize($details);

        if (null !== $identity && ! $identity instanceof IdentityInterface) {
            throw new UnexpectedValueException(sprintf('The details of token "%s" in "%s" are not a serialized %s, so they cannot be converted to JSON.', $hash, $table, IdentityInterface::class));
        }

        return $identity;
    }
}
