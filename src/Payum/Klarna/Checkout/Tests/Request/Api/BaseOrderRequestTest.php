<?php
namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\BaseOrderRequest;

class BaseOrderRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelAware()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\BaseOrderRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelAware'));
    }

    /**
     * @test
     */
    public function shouldBeAbstractClass()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\BaseModelInteractiveRequest');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAsArgument()
    {
        $this->createBaseOrderRequestMock(array());
        $this->createBaseOrderRequestMock(new \ArrayObject());
        $this->createBaseOrderRequestMock($this->getMock('ArrayAccess'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Given model is invalid. Should be an array or ArrayAccess instance.
     */
    public function throwIfTryConstructWithNotArrayModel()
    {
        $this->createBaseOrderRequestMock('not array');
    }

    /**
     * @test
     */
    public function shouldAllowSetOrder()
    {
        $request = $this->createBaseOrderRequestMock(array());

        $expectedOrder = $this->createOrderMock();

        $request->setOrder($expectedOrder);

        $this->assertAttributeSame($expectedOrder, 'order', $request);
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetOrder()
    {
        $request = $this->createBaseOrderRequestMock(array());

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

    /**
     * @param array $arguments
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|BaseOrderRequest
     */
    protected function createBaseOrderRequestMock($model)
    {
        return $this->getMockForAbstractClass('Payum\Klarna\Checkout\Request\Api\BaseOrderRequest', array($model));
    }
}