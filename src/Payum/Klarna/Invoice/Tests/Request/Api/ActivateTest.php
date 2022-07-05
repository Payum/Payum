<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\Activate;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ActivateTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new ReflectionClass(Activate::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
