<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\Api\FetchOrderAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Request\Api\FetchOrder;
use Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction;
use Payum\Core\Request\Generic;

class FetchOrderActionTest extends GenericActionTest
{
    protected $requestClass = FetchOrder::class;

    protected $actionClass = FetchOrderAction::class;

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass(Generic::class, array(array())));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass(FetchOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    /**
     * @test
     */
    public function throwIfLocationNotSetOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Location has to be provided to fetch an order');
        $action = new FetchOrderAction();

        $action->execute(new FetchOrder(array()));
    }

    /**
     * @test
     */
    public function shouldFetchOrderWhenLocationSetOnExecute()
    {
        $model = array(
            'location' => 'theKlarnaOrderLocation',
        );

        $request = new FetchOrder($model);

        $connector = $this->createConnectorMock();

        $testCase = $this;

        $connector
            ->expects($this->once())
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

    /**
     * @test
     */
    public function shouldReturnSameOrderUsedWhileFetchAndUpdateCallsOnExecute()
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

        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->once())
            ->method('apply')
            ->with('GET')
            ->willReturnCallback(function ($method, $order) use (&$expectedOrder) {
                $expectedOrder = $order;
            })
        ;

        $action = new FetchOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertSame($expectedOrder, $request->getOrder());
    }

    /**
     * @test
     */
    public function shouldFailedAfterThreeRetriesOnTimeout()
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

    /**
     * @test
     */
    public function shouldRecoverAfterTimeout()
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
            ->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(['GET'], ['GET'])
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \Klarna_Checkout_ConnectionErrorException()),
                $this->returnCallback(function ($method, $order, $options) use (&$expectedOrder) {
                    $expectedOrder = $order;
                })
            )
        ;

        $action = new FetchOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request = new FetchOrder($model));

        $this->assertSame($expectedOrder, $request->getOrder());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->createMock('Klarna_Checkout_ConnectorInterface', array(), array(), '', false);
    }
}
