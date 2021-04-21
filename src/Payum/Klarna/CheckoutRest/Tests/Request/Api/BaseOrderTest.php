<?php
namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\BaseOrder;
use PHPUnit\Framework\TestCase;

class BaseOrderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\BaseOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    /**
     * @test
     */
    public function shouldBeAbstractClass()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\BaseOrder');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAsArgument()
    {
        $this->createBaseOrderMock(array());
        $this->createBaseOrderMock(new \ArrayObject());
        $this->createBaseOrderMock($this->createMock('ArrayAccess'));
    }

    /**
     * @test
     */
    public function throwIfTryConstructWithNotArrayModel()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Given model is invalid. Should be an array or ArrayAccess instance.');
        $this->createBaseOrderMock('not array');
    }

    /**
     * @test
     */
    public function shouldAllowSetOrder()
    {
        $request = $this->createBaseOrderMock(array());

        $expectedOrder = $this->createOrderMock();

        $request->setOrder($expectedOrder);

        $this->assertAttributeSame($expectedOrder, 'order', $request);
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetOrder()
    {
        $request = $this->createBaseOrderMock(array());

        $expectedOrder = $this->createOrderMock();

        $request->setOrder($expectedOrder);

        $this->assertSame($expectedOrder, $request->getOrder());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->createMock('Klarna_Checkout_Order', array(), array(), '', false);
    }

    /**
     * @param array $arguments
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|BaseOrder
     */
    protected function createBaseOrderMock($model)
    {
        return $this->getMockForAbstractClass('Payum\Klarna\Checkout\Request\Api\BaseOrder', array($model));
    }
}
