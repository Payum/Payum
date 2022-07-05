<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\EmailInvoice;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EmailInvoiceTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new ReflectionClass(EmailInvoice::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
