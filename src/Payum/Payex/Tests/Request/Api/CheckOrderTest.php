<?php

namespace Payum\Payex\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Payex\Request\Api\CheckOrder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CheckOrderTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(CheckOrder::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
