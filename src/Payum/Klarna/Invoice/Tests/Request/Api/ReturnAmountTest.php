<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\ReturnAmount;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ReturnAmountTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new ReflectionClass(ReturnAmount::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
