<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\CreditPart;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CreditPartTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new ReflectionClass(CreditPart::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
