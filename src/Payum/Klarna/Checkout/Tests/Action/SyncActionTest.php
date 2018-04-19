<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\SyncAction;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\FetchOrder;

class SyncActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Klarna\Checkout\Action\SyncAction';

    protected $requestClass = 'Payum\Core\Request\Sync';
    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\SyncAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\GatewayAwareAction'));
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
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new SyncAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSubExecuteFetchOrderRequestIfModelHasLocationSet()
    {
        $orderMock = $this->getMock('Klarna_Checkout_Order', array('marshal'), array(), '', false);
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->will($this->returnValue(array('foo' => 'fooVal', 'bar' => 'barVal')))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\FetchOrder'))
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
        return $this->getMock('Payum\Core\GatewayInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->getMock('Klarna_Checkout_Order', array(), array(), '', false);
    }
}
