<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\Api\CreateOrderAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;
use Payum\Core\Request\Generic;
use Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction;

class CreateOrderActionTest extends GenericActionTest
{
    protected $requestClass = CreateOrder::class;

    protected $actionClass = CreateOrderAction::class;

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass(Generic::class, array(array())));
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass(CreateOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    public function testShouldCreateOrderOnExecute()
    {
        $request = new CreateOrder(array());

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->once())
            ->method('apply')
            ->with('POST')
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertInstanceOf('Klarna_Checkout_Order', $request->getOrder());
    }

    public function testShouldUseModelAsDataToCreateOrderOnExecute()
    {
        $model = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
            'merchant' => array('id' => 'anId'),
        );

        $request = new CreateOrder($model);

        $testCase = $this;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->once())
            ->method('apply')
            ->with('POST')
            ->willReturnCallback(function ($method, $order, $options) use ($testCase, $model) {
                $testCase->assertIsArray($options);
                $testCase->assertArrayHasKey('data', $options);
                $testCase->assertSame($model, $options['data']);
            })
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertInstanceOf('Klarna_Checkout_Order', $request->getOrder());
    }

    public function testShouldAddMerchantIdFromConfigIfNotSetInModelOnExecute()
    {
        $config = new Config();
        $config->merchantId = 'theMerchantId';

        $model = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );

        $expectedModel = $model;
        $expectedModel['merchant']['id'] = 'theMerchantId';

        $request = new CreateOrder($model);

        $testCase = $this;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->once())
            ->method('apply')
            ->with('POST')
            ->willReturnCallback(function ($method, $order, $options) use ($testCase, $expectedModel) {
                $testCase->assertIsArray($options);
                $testCase->assertArrayHasKey('data', $options);
                $testCase->assertSame($expectedModel, $options['data']);
            })
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi($config);

        $action->execute($request);

        $this->assertInstanceOf('Klarna_Checkout_Order', $request->getOrder());
    }

    public function testShouldReturnSameOrderUsedWhileCreateAndFetchCallsOnExecute()
    {
        $request = new CreateOrder(array());

        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->once(0))
            ->method('apply')
            ->with('POST')
            ->willReturnCallback(function ($method, $order) use (&$expectedOrder) {
                $expectedOrder = $order;
            })
        ;

        $action = new CreateOrderAction($connector);
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

        $request = new CreateOrder($model);

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->exactly(3))
            ->method('apply')
            ->with('POST')
            ->willThrowException(new \Klarna_Checkout_ConnectionErrorException())
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);
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

        $request = new CreateOrder($model);

        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->exactly(2))
            ->method('apply')
            ->with('POST')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \Klarna_Checkout_ConnectionErrorException()),
                $this->returnCallback(function ($method, $order, $options) use (&$expectedOrder) {
                    $expectedOrder = $order;
                })
            )
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

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
