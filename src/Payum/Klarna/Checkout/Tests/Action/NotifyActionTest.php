<?php

namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\NotifyAction;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;

class NotifyActionTest extends GenericActionTest
{
    protected $actionClass = NotifyAction::class;

    protected $requestClass = Notify::class;

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(NotifyAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldUpdateOrderWithStatusCreatedIfCurrentStatusCheckoutCompleteOnExecute()
    {
        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(UpdateOrder::class)],
                [$this->isInstanceOf(Sync::class)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->returnCallback(function (UpdateOrder $request) use ($testCase) {
                    $model = $request->getModel();

                    $testCase->assertSame(Constants::STATUS_CREATED, $model['status']);
                    $testCase->assertSame('theLocation', $model['location']);
                    $testCase->assertSame('theOrderId', $model['merchant_reference']['orderid1']);
                }),
                null
            )
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Notify([
            'status' => Constants::STATUS_CHECKOUT_COMPLETE,
            'location' => 'theLocation',
            'merchant_reference' => [
                'orderid1' => 'theOrderId',
            ],
        ]));
    }

    public function testShouldNotUpdateOrderWithStatusCreatedIfCurrentStatusCheckoutInCompleteOnExecute()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Notify([
            'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
            'location' => 'aLocation',
        ]));
    }

    public function testShouldNotUpdateOrderWithStatusCreatedIfCurrentStatusCreatedOnExecute()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Notify([
            'status' => Constants::STATUS_CREATED,
            'location' => 'aLocation',
        ]));
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->createMock(\Klarna_Checkout_Order::class, [], [], '', false);
    }
}
