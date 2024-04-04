<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\Api\FetchOrderAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Request\Api\FetchOrder;

class FetchOrderActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Klarna\Checkout\Request\Api\FetchOrder';

    protected $actionClass = 'Payum\Klarna\Checkout\Action\Api\FetchOrderAction';

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array())));
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\FetchOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction'));
    }

    public function testThrowIfLocationNotSetOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Location has to be provided to fetch an order');
        $action = new FetchOrderAction();

        $action->execute(new FetchOrder(array()));
    }

    public function testShouldFetchOrderWhenLocationSetOnExecute()
    {
        $model = array(
            'location' => 'theKlarnaOrderLocation',
        );

        $request = new FetchOrder($model);

        $connector = $this->createConnectorMock();

        $testCase = $this;

        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('GET')
            ->willReturnCallback(function ($method, $order, $options) use ($testCase, $model) {
                $testCase->assertIsArray($options);
                $testCase->assertArrayHasKey('url', $options);
                $testCase->assertSame($model['location'], $options['url']);
            })
        ;

        $action = new FetchOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertInstanceOf('Klarna_Checkout_Order', $request->getOrder());
    }

    public function testShouldReturnSameOrderUsedWhileFetchAndUpdateCallsOnExecute()
    {
        $model = array(
            'location' => 'theKlarnaOrderLocation',
            'cart' => array(
                'items' => array(
                    array('foo'),
                    array('bar'),
                ),
            ),
        );

        $request = new FetchOrder($model);

        $testCase = $this;
        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('GET')
            ->willReturnCallback(function ($method, $order, $options) use ($testCase, &$expectedOrder) {
                $expectedOrder = $order;
            })
        ;

        $action = new FetchOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertSame($expectedOrder, $request->getOrder());
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
            ->with('GET')
            ->willThrowException(new \Klarna_Checkout_ConnectionErrorException())
        ;

        $action = new FetchOrderAction($connector);
        $action->setApi(new Config());

        $action->execute(new FetchOrder($model));
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

        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('GET')
            ->willThrowException(new \Klarna_Checkout_ConnectionErrorException())
        ;
        $connector
            ->expects($this->at(1))
            ->method('apply')
            ->with('GET')
            ->willReturnCallback(function ($method, $order, $options) use (&$expectedOrder) {
                $expectedOrder = $order;
            })
        ;

        $action = new FetchOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request = new FetchOrder($model));

        $this->assertSame($expectedOrder, $request->getOrder());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->createMock('Klarna_Checkout_ConnectorInterface', array(), array(), '', false);
    }
}
