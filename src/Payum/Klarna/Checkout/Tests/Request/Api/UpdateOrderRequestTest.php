<?php
namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\UpdateOrderRequest;

class UpdateOrderRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseOrderRequest()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\UpdateOrderRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Checkout\Request\Api\BaseOrderRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAsArgument()
    {
        new UpdateOrderRequest(array());
    }
}