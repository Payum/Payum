<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class NotifyTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(Notify::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
