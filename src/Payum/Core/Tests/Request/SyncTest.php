<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SyncTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(Sync::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
