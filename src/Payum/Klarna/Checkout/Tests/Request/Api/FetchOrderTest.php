<?php
namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\FetchOrder;
use PHPUnit\Framework\TestCase;

class FetchOrderTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\FetchOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Checkout\Request\Api\BaseOrder'));
    }
}
