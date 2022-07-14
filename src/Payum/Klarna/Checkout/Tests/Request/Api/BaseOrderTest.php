<?php

namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Klarna_Checkout_Order;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Request\Generic;
use Payum\Klarna\Checkout\Request\Api\BaseOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BaseOrderTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(BaseOrder::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testShouldBeAbstractClass(): void
    {
        $rc = new ReflectionClass(BaseOrder::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testThrowIfTryConstructWithNotArrayModel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Given model is invalid. Should be an array or ArrayAccess instance.');
        $this->createBaseOrderMock('not array');
    }

    public function testShouldAllowGetPreviouslySetOrder(): void
    {
        $request = $this->createBaseOrderMock([]);

        $expectedOrder = $this->createOrderMock();

        $request->setOrder($expectedOrder);

        $this->assertSame($expectedOrder, $request->getOrder());
    }

    /**
     * @return MockObject|Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->createMock(Klarna_Checkout_Order::class, [], [], '', false);
    }

    /**
     * @return MockObject|BaseOrder
     */
    protected function createBaseOrderMock($model)
    {
        return $this->getMockForAbstractClass(BaseOrder::class, [$model]);
    }
}
