<?php

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\Refund;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RefundTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(Refund::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
