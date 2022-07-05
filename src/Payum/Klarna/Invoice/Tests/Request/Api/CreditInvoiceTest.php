<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\CreditInvoice;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CreditInvoiceTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new ReflectionClass(CreditInvoice::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
