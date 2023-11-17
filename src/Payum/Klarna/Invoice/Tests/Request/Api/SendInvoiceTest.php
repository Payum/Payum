<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\SendInvoice;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SendInvoiceTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder(): void
    {
        $rc = new ReflectionClass(SendInvoice::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
