<?php

declare(strict_types=1);

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\EmailInvoice;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class EmailInvoiceTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder(): void
    {
        $rc = new ReflectionClass(EmailInvoice::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
