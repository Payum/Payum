<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

class SendInvoiceTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Invoice\Request\Api\SendInvoice::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Request\Generic::class));
    }
}
