<?php

namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Iterator;
use Klarna_Checkout_ConnectionErrorException;
use Klarna_Checkout_ConnectorInterface;
use Klarna_Checkout_Order;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\Generic;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Checkout\Action\Api\FetchOrderAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Request\Api\FetchOrder;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use stdClass;

class FetchOrderActionTest extends GenericActionTest
{
    protected $requestClass = FetchOrder::class;

    protected $actionClass = FetchOrderAction::class;

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
    }

    public function testShouldBeSubClassOfBaseApiAwareAction(): void
    {
        $rc = new ReflectionClass(FetchOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    public function testThrowIfLocationNotSetOnExecute(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Location has to be provided to fetch an order');
        $action = new FetchOrderAction();

        $action->execute(new FetchOrder([]));
    }

    public function testShouldFetchOrderWhenLocationSetOnExecute(): void
    {
        $model = [
            'location' => 'theKlarnaOrderLocation',
        ];

        $request = new FetchOrder($model);

        $connector = $this->createConnectorMock();

        $testCase = $this;

        $connector
            ->expects($this->once())
            ->method('apply')
            ->with('GET')
            ->willReturnCallback(function ($method, $order, $options) use ($testCase, $model): void {
                $testCase->assertIsArray($options);
                $testCase->assertArrayHasKey('url', $options);
                $testCase->assertSame($model['location'], $options['url']);
            })
        ;

        $action = new FetchOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertInstanceOf(Klarna_Checkout_Order::class, $request->getOrder());
    }

    public function testShouldReturnSameOrderUsedWhileFetchAndUpdateCallsOnExecute(): void
    {
        $model = [
            'location' => 'theKlarnaOrderLocation',
            'cart' => [
                'items' => [
                    ['foo'],
                    ['bar'],
                ],
            ],
        ];

        $request = new FetchOrder($model);

        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->once())
            ->method('apply')
            ->with('GET')
            ->willReturnCallback(function ($method, $order) use (&$expectedOrder): void {
                $expectedOrder = $order;
            })
        ;

        $action = new FetchOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertSame($expectedOrder, $request->getOrder());
    }

    public function testShouldFailedAfterThreeRetriesOnTimeout(): void
    {
        $this->expectException(Klarna_Checkout_ConnectionErrorException::class);
        $model = [
            'location' => 'theLocation',
            'cart' => [
                'items' => [
                    ['foo'],
                    ['bar'],
                ],
            ],
        ];

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->exactly(3))
            ->method('apply')
            ->with('GET')
            ->willThrowException(new Klarna_Checkout_ConnectionErrorException())
        ;

        $action = new FetchOrderAction($connector);
        $action->setApi(new Config());

        $action->execute(new FetchOrder($model));
    }

    public function testShouldRecoverAfterTimeout(): void
    {
        $model = [
            'location' => 'theLocation',
            'cart' => [
                'items' => [
                    ['foo'],
                    ['bar'],
                ],
            ],
        ];

        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(['GET'], ['GET'])
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new Klarna_Checkout_ConnectionErrorException()),
                $this->returnCallback(function ($method, $order, $options) use (&$expectedOrder): void {
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
     * @return MockObject|Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->createMock(Klarna_Checkout_ConnectorInterface::class);
    }
}
