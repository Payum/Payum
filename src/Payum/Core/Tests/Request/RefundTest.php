<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\Refund;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class RefundTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(Refund::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
