<?php
namespace Payum\Klarna\CheckoutRest\Tests\Request\Api;

use Payum\Klarna\CheckoutRest\Request\Api\CreateOrder;
use PHPUnit\Framework\TestCase;

class CreateOrderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\CheckoutRest\Request\Api\CreateOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\CheckoutRest\Request\Api\BaseOrder'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAsArgument()
    {
        new CreateOrder(array());
    }
}
