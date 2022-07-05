<?php

namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\BaseOrder;
use PHPUnit\Framework\TestCase;

class BaseOrderTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Checkout\Request\Api\BaseOrder::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Request\Generic::class));
    }

    public function testShouldBeAbstractClass()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Checkout\Request\Api\BaseOrder::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testThrowIfTryConstructWithNotArrayModel()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Given model is invalid. Should be an array or ArrayAccess instance.');
        $this->createBaseOrderMock('not array');
    }

    public function testShouldAllowGetPreviouslySetOrder()
    {
        $request = $this->createBaseOrderMock([]);

        $expectedOrder = $this->createOrderMock();

        $request->setOrder($expectedOrder);

        $this->assertSame($expectedOrder, $request->getOrder());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->createMock(\Klarna_Checkout_Order::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|BaseOrder
     */
    protected function createBaseOrderMock($model)
    {
        return $this->getMockForAbstractClass(\Payum\Klarna\Checkout\Request\Api\BaseOrder::class, [$model]);
    }
}
