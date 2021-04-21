<?php
namespace Payum\Klarna\CheckoutRest\Tests\Request\Api;

use Payum\Klarna\CheckoutRest\Request\Api\UpdateOrder;
use PHPUnit\Framework\TestCase;

class UpdateOrderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\CheckoutRest\Request\Api\UpdateOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\CheckoutRest\Request\Api\BaseOrder'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAsArgument()
    {
        new UpdateOrder(array());
    }
}
