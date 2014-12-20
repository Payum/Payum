<?php
namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\UpdateOrder;

class UpdateOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\UpdateOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Checkout\Request\Api\BaseOrder'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAsArgument()
    {
        new UpdateOrder(array());
    }
}
