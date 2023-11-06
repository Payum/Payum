<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\CreditInvoice;

class CreditInvoiceTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Request\Api\CreditInvoice');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
