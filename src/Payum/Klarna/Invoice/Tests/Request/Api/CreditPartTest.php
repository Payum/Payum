<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\CreditPart;

class CreditPartTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Request\Api\CreditPart');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
