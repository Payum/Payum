<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Doctrine\Idempotency;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Payum\Core\Idempotency\IdempotencyStoreInterface;
use Payum\Core\Model\IdempotencyRecord;

/**
 * Keeps records in a table of their own, over a plain DBAL connection.
 *
 * Create the table with {@see \Payum\Core\Bridge\Doctrine\Migrations\AbstractCreateIdempotencyTableMigration}
 * or {@see self::configureSchema()}.
 */
final class DbalIdempotencyStore implements IdempotencyStoreInterface
{
    public const DEFAULT_TABLE = 'payum_idempotency_record';

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table = self::DEFAULT_TABLE,
    ) {
    }

    /**
     * Adds the table to $schema unless it is already there.
     */
    public function configureSchema(Schema $schema): void
    {
        if ($schema->hasTable($this->table)) {
            return;
        }

        $table = $schema->createTable($this->table);
        $table->addColumn('scope', Types::STRING, [
            'length' => 64,
        ]);
        $table->addColumn('fingerprint', Types::STRING, [
            'length' => 64,
        ]);
        $table->addColumn('result', Types::TEXT);
        // A Unix timestamp, so the expiry means the same instant whatever timezone the database runs in.
        $table->addColumn('expires_at', Types::BIGINT);
        $table->setPrimaryKey(['scope']);
        $table->addIndex(['expires_at'], $this->table . '_expires_at');
    }

    public function find(string $scope): ?IdempotencyRecord
    {
        $row = $this->connection->fetchAssociative(
            sprintf('SELECT fingerprint, result, expires_at FROM %s WHERE scope = ?', $this->table),
            [$scope],
        );

        if (false === $row) {
            return null;
        }

        $record = new IdempotencyRecord();
        $record->setScope($scope);
        $record->setFingerprint((string) $row['fingerprint']);
        $record->setResult((array) json_decode((string) $row['result'], true, 512, JSON_THROW_ON_ERROR));
        $record->setExpiresAt((new DateTimeImmutable())->setTimestamp((int) $row['expires_at']));

        return $record;
    }

    public function save(IdempotencyRecord $record): void
    {
        $values = [
            'fingerprint' => $record->getFingerprint(),
            'result' => json_encode($record->getResult(), JSON_THROW_ON_ERROR),
            'expires_at' => $record->getExpiresAt()->getTimestamp(),
        ];

        if (0 < (int) $this->connection->update($this->table, $values, [
            'scope' => $record->getScope(),
        ])) {
            return;
        }

        try {
            $this->connection->insert($this->table, [
                'scope' => $record->getScope(),
            ] + $values);
        } catch (UniqueConstraintViolationException) {
            // MySQL counts an update that changed nothing as no rows, and another request may have
            // inserted the row in between.
            $this->connection->update($this->table, $values, [
                'scope' => $record->getScope(),
            ]);
        }
    }

    /**
     * Deletes the records that expired before $now. Run it from a scheduled job to keep the table small.
     *
     * @return int how many records were deleted
     */
    public function deleteExpired(DateTimeInterface $now): int
    {
        return (int) $this->connection->executeStatement(
            sprintf('DELETE FROM %s WHERE expires_at < ?', $this->table),
            [$now->getTimestamp()],
        );
    }
}
