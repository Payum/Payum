<?php

namespace Payum\Payex\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Payex\Request\Api\CompleteOrder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CompleteOrderTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(CompleteOrder::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
