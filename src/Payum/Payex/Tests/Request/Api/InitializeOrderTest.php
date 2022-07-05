<?php

namespace Payum\Payex\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Payex\Request\Api\InitializeOrder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class InitializeOrderTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(InitializeOrder::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
