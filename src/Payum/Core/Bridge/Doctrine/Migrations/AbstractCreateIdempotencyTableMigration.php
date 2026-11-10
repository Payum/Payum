<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Payum\Core\Bridge\Doctrine\Idempotency\DbalIdempotencyStore;

/**
 * Creates the table DbalIdempotencyStore keeps its records in.
 *
 * Extend it in your application. Override tableName() if you passed the store a table of your own.
 */
abstract class AbstractCreateIdempotencyTableMigration extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the table Payum keeps idempotency records in';
    }

    public function up(Schema $schema): void
    {
        (new DbalIdempotencyStore($this->connection, $this->tableName()))->configureSchema($schema);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable($this->tableName());
    }

    protected function tableName(): string
    {
        return DbalIdempotencyStore::DEFAULT_TABLE;
    }
}
