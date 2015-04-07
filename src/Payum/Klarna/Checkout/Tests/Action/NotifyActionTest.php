<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Notify;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\NotifyAction;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;

class NotifyActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Klarna\Checkout\Action\NotifyAction';

    protected $requestClass = 'Payum\Core\Request\Notify';

    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\NotifyAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\GatewayAwareAction'));
    }

    /**
     * @test
     */
    public function shouldUpdateOrderWithStatusCreatedIfCurrentStatusCheckoutCompleteOnExecute()
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
            ->will($this->returnCallback(function (UpdateOrder $request) use ($testCase) {
                $model = $request->getModel();

                $testCase->assertEquals(Constants::STATUS_CREATED, $model['status']);
                $testCase->assertEquals('theLocation', $model['location']);
                $testCase->assertEquals('theOrderId', $model['merchant_reference']['orderid1']);
            }))
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

    /**
     * @test
     */
    public function shouldNotUpdateOrderWithStatusCreatedIfCurrentStatusCheckoutInCompleteOnExecute()
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

    /**
     * @test
     */
    public function shouldNotUpdateOrderWithStatusCreatedIfCurrentStatusCreatedOnExecute()
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
