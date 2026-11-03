<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Migrations\Fixtures;

use Payum\Core\Bridge\Doctrine\Migrations\AbstractTokenDetailsToJsonMigration;

final class Version20261103000000 extends AbstractTokenDetailsToJsonMigration
{
    protected function tokenTables(): array
    {
        return ['Token'];
    }
}
