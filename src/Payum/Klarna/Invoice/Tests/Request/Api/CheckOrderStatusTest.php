<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\CheckOrderStatus;

class CheckOrderStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Request\Api\CheckOrderStatus');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
