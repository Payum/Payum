<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Cancel;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CancelTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(Cancel::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
