<?php
namespace Payum\Klarna\CheckoutRest\Tests\Action;

use Klarna\Rest\Checkout\Order;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\CheckoutRest\Action\SyncAction;
use Payum\Klarna\CheckoutRest\Request\Api\FetchOrder;
use Payum\Klarna\Common\Constants;

class SyncActionTest extends GenericActionTest
{
    protected $actionClass = SyncAction::class;

    protected $requestClass = Sync::class;

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(SyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new SyncAction();
    }

    /**
     * @test
     */
    public function shouldSupportSyncWithArrayAsModel()
    {
        $action = new SyncAction();

        $this->assertTrue($action->supports(new Sync(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSync()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportSyncWithNotArrayAccessModel()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new Sync(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new SyncAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSubExecuteFetchOrderRequestIfModelHasLocationSet()
    {
        $orderMock = $this->createMock(Order::class, array('marshal'), array(), '', false);
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->willReturn(array('foo' => 'fooVal', 'bar' => 'barVal'))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(FetchOrder::class))
            ->will($this->returnCallback(function (FetchOrder $request) use ($orderMock) {
                $request->setOrder($orderMock);
            }))
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync(array(
            'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
            'location' => 'theLocation',
        ));

        $action->execute($request);

        $model = $request->getModel();
        $this->assertEquals('theLocation', $model['location']);
        $this->assertEquals(Constants::STATUS_CHECKOUT_INCOMPLETE, $model['status']);
        $this->assertEquals('fooVal', $model['foo']);
        $this->assertEquals('barVal', $model['bar']);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfModelHasNotLocationSet()
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
     * @return \PHPUnit_Framework_MockObject_MockObject|Order
     */
    protected function createOrderMock()
    {
        return $this->createMock(Order::class, array(), array(), '', false);
    }
}
