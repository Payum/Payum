<?php
namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\CreateOrder;
use PHPUnit\Framework\TestCase;

class CreateOrderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\CreateOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Checkout\Request\Api\BaseOrder'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAsArgument()
    {
        new CreateOrder(array());
    }
}
