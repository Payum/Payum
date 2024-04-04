<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\ReserveAmount;

class ReserveAmountTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Request\Api\ReserveAmount');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
