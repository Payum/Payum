<?php
namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\CreateOrderRequest;

class CreateOrderRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\CreateOrderRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAsArgument()
    {
        new CreateOrderRequest(array());
        new CreateOrderRequest(new \ArrayObject());
        new CreateOrderRequest($this->getMock('ArrayAccess'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Given model is invalid. Should be an array or ArrayAccess instance.
     */
    public function throwIfTryConstructWithNotArrayModel()
    {
        new CreateOrderRequest('not array');
    }

    /**
     * @test
     */
    public function shouldAllowSetOrder()
    {
        $request = new CreateOrderRequest(array());

        $expectedOrder = $this->createOrderMock();

        $request->setOrder($expectedOrder);

        $this->assertAttributeSame($expectedOrder, 'order', $request);
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetOrder()
    {
        $request = new CreateOrderRequest(array());

        $expectedOrder = $this->createOrderMock();

        $request->setOrder($expectedOrder);

        $this->assertSame($expectedOrder, $request->getOrder());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->getMock('Klarna_Checkout_Order', array(), array(), '', false);
    }
}