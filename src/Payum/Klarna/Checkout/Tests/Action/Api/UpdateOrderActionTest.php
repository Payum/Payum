<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\Api\UpdateOrderAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;

class UpdateOrderActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Klarna\Checkout\Request\Api\UpdateOrder';

    protected $actionClass = 'Payum\Klarna\Checkout\Action\Api\UpdateOrderAction';

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array())));
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\UpdateOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction'));
    }

    public function testShouldUpdateOrderIfModelHasCartItemsSetOnExecute()
    {
        $model = array(
            'location' => 'theLocation',
            'cart' => array(
                'items' => array(
                    array('foo'),
                    array('bar'),
                ),
            ),
        );

        $request = new UpdateOrder($model);

        $testCase = $this;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('POST')
            ->willReturnCallback(function ($method, $order, $options) use ($testCase, $model) {
                $testCase->assertIsArray($options);
                $testCase->assertArrayHasKey('data', $options);
                $testCase->assertSame(array('cart' => $model['cart']), $options['data']);
            })
        ;

        $action = new UpdateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertInstanceOf('Klarna_Checkout_Order', $request->getOrder());
    }

    public function testShouldFailedAfterThreeRetriesOnTimeout()
    {
        $this->expectException(\Klarna_Checkout_ConnectionErrorException::class);
        $model = array(
            'location' => 'theLocation',
            'cart' => array(
                'items' => array(
                    array('foo'),
                    array('bar'),
                ),
            ),
        );

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->exactly(3))
            ->method('apply')
            ->with('POST')
            ->willThrowException(new \Klarna_Checkout_ConnectionErrorException())
        ;

        $action = new UpdateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute(new UpdateOrder($model));
    }

    public function testShouldRecoverAfterTimeout()
    {
        $model = array(
            'location' => 'theLocation',
            'cart' => array(
                'items' => array(
                    array('foo'),
                    array('bar'),
                ),
            ),
        );

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('POST')
            ->willThrowException(new \Klarna_Checkout_ConnectionErrorException())
        ;
        $connector
            ->expects($this->at(1))
            ->method('apply')
            ->with('POST')
            ->willReturnCallback(function ($method, $order, $options) use ($model) {
                $this->assertIsArray($options);
                $this->assertArrayHasKey('data', $options);
                $this->assertSame(array('cart' => $model['cart']), $options['data']);
            })
        ;

        $action = new UpdateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request = new UpdateOrder($model));

        $this->assertInstanceOf('Klarna_Checkout_Order', $request->getOrder());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->createMock('Klarna_Checkout_ConnectorInterface', array(), array(), '', false);
    }
}
