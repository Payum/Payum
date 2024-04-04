<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\SyncAction;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\FetchOrder;

class SyncActionTest extends GenericActionTest
{
    protected $actionClass = SyncAction::class;

    protected $requestClass = Sync::class;

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(SyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncWithArrayAsModel()
    {
        $action = new SyncAction();

        $this->assertTrue($action->supports(new Sync(array())));
    }

    public function testShouldNotSupportAnythingNotSync()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportSyncWithNotArrayAccessModel()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new Sync(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new SyncAction();

        $action->execute(new \stdClass());
    }

    public function testShouldSubExecuteFetchOrderRequestIfModelHasLocationSet()
    {
        $orderMock = $this->createMock('Klarna_Checkout_Order', array('marshal'), array(), '', false);
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->willReturn(array('foo' => 'fooVal', 'bar' => 'barVal'))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\FetchOrder'))
            ->willReturnCallback(function (FetchOrder $request) use ($orderMock) {
                $request->setOrder($orderMock);
            })
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync(array(
            'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
            'location' => 'theLocation',
        ));

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theLocation', $model['location']);
        $this->assertSame(Constants::STATUS_CHECKOUT_INCOMPLETE, $model['status']);
        $this->assertSame('fooVal', $model['foo']);
        $this->assertSame('barVal', $model['bar']);
    }

    public function testShouldDoNothingIfModelHasNotLocationSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync(array());

        $action->execute($request);
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
