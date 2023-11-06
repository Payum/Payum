<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Notify;
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
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\UpdateOrder'))
            ->willReturnCallback(function (UpdateOrder $request) use ($testCase) {
                $model = $request->getModel();

                $testCase->assertSame(Constants::STATUS_CREATED, $model['status']);
                $testCase->assertSame('theLocation', $model['location']);
                $testCase->assertSame('theOrderId', $model['merchant_reference']['orderid1']);
            })
        ;
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Notify(array(
            'status' => Constants::STATUS_CHECKOUT_COMPLETE,
            'location' => 'theLocation',
            'merchant_reference' => array(
                'orderid1' => 'theOrderId',
            ),
        )));
    }

    public function testShouldNotUpdateOrderWithStatusCreatedIfCurrentStatusCheckoutInCompleteOnExecute()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Notify(array(
            'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
            'location' => 'aLocation',
        )));
    }

    public function testShouldNotUpdateOrderWithStatusCreatedIfCurrentStatusCreatedOnExecute()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Notify(array(
            'status' => Constants::STATUS_CREATED,
            'location' => 'aLocation',
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->createMock('Klarna_Checkout_Order', array(), array(), '', false);
    }
}
