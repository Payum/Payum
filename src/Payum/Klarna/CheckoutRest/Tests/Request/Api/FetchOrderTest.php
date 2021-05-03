<?php
namespace Payum\Klarna\CheckoutRest\Tests\Request\Api;

use Payum\Klarna\CheckoutRest\Request\Api\FetchOrder;
use PHPUnit\Framework\TestCase;

class FetchOrderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\CheckoutRest\Request\Api\FetchOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\CheckoutRest\Request\Api\BaseOrder'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAsArgument()
    {
        new FetchOrder(array());
    }
}
