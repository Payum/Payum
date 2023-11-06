<?php
namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\UpdateOrder;
use PHPUnit\Framework\TestCase;

class UpdateOrderTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\UpdateOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Checkout\Request\Api\BaseOrder'));
    }
}
