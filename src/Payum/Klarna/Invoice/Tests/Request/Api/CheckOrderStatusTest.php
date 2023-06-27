<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\CheckOrderStatus;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CheckOrderStatusTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder(): void
    {
        $rc = new ReflectionClass(CheckOrderStatus::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
