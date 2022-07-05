<?php

namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Iterator;
use Klarna_Checkout_ConnectionErrorException;
use Klarna_Checkout_ConnectorInterface;
use Klarna_Checkout_Order;
use Payum\Core\Request\Generic;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Checkout\Action\Api\UpdateOrderAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use stdClass;

class UpdateOrderActionTest extends GenericActionTest
{
    protected $requestClass = UpdateOrder::class;

    protected $actionClass = UpdateOrderAction::class;

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new ReflectionClass(UpdateOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    public function testShouldUpdateOrderIfModelHasCartItemsSetOnExecute()
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

        $request = new UpdateOrder($model);

        $testCase = $this;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->exactly(1))
            ->method('apply')
            ->with('POST')
            ->willReturnCallback(function ($method, $order, $options) use ($testCase, $model) {
                $testCase->assertIsArray($options);
                $testCase->assertArrayHasKey('data', $options);
                $testCase->assertEquals([
                    'cart' => $model['cart'],
                ], $options['data']);
            })
        ;

        $action = new UpdateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertInstanceOf(Klarna_Checkout_Order::class, $request->getOrder());
    }

    public function testShouldFailedAfterThreeRetriesOnTimeout()
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
            ->with('POST')
            ->willThrowException(new Klarna_Checkout_ConnectionErrorException())
        ;

        $action = new UpdateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute(new UpdateOrder($model));
    }

    public function testShouldRecoverAfterTimeout()
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

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->exactly(2))
            ->method('apply')
            ->withConsecutive(['POST'], ['POST'])
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new Klarna_Checkout_ConnectionErrorException()),
                $this->returnCallback(function ($method, $order, $options) use ($model) {
                    $this->assertIsArray($options);
                    $this->assertArrayHasKey('data', $options);
                    $this->assertEquals([
                        'cart' => $model['cart'],
                    ], $options['data']);
                })
            )
        ;

        $action = new UpdateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request = new UpdateOrder($model));

        $this->assertInstanceOf(Klarna_Checkout_Order::class, $request->getOrder());
    }

    /**
     * @return MockObject|Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->createMock(Klarna_Checkout_ConnectorInterface::class, [], [], '', false);
    }
}
