<?php
namespace Payum\Klarna\CheckoutRest\Tests\Action\Api;

use Payum\Core\Request\Generic;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\CheckoutRest\Action\Api\CreateOrderAction;
use Payum\Klarna\CheckoutRest\Request\Api\CreateOrder;
use Payum\Klarna\Common\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Common\Config;

class CreateOrderActionTest extends GenericActionTest
{
    protected $requestClass = CreateOrder::class;

    protected $actionClass = CreateOrderAction::class;

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array())));
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass(CreateOrderAction::class);

        $rc->isSubclassOf(BaseApiAwareAction::class);
    }

    /**
     * @test
     */
    public function shouldCreateOrderOnExecute()
    {
        $request = new CreateOrder(array());

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(1))
            ->method('apply')
            ->with('POST')
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertInstanceOf(\Klarna_Checkout_Order::class, $request->getOrder());
    }

    /**
     * @test
     */
    public function shouldUseModelAsDataToCreateOrderOnExecute()
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
            ->expects($this->at(1))
            ->method('apply')
            ->with('POST')
            ->will($this->returnCallback(function ($method, $order, $options) use ($testCase, $model) {
                $testCase->assertIsArray($options);
                $testCase->assertArrayHasKey('data', $options);
                $testCase->assertEquals($model, $options['data']);
            }))
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertInstanceOf(\Klarna_Checkout_Order::class, $request->getOrder());
    }

    /**
     * @test
     */
    public function shouldAddMerchantIdFromConfigIfNotSetInModelOnExecute()
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
            ->expects($this->at(1))
            ->method('apply')
            ->with('POST')
            ->will($this->returnCallback(function ($method, $order, $options) use ($testCase, $expectedModel) {
                $testCase->assertIsArray($options);
                $testCase->assertArrayHasKey('data', $options);
                $testCase->assertEquals($expectedModel, $options['data']);
            }))
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi($config);

        $action->execute($request);

        $this->assertInstanceOf(\Klarna_Checkout_Order::class, $request->getOrder());
    }

    /**
     * @test
     */
    public function shouldReturnSameOrderUsedWhileCreateAndFetchCallsOnExecute()
    {
        $request = new CreateOrder(array());

        $testCase = $this;
        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('POST')
            ->will($this->returnCallback(function ($method, $order, $options) use ($testCase, &$expectedOrder) {
                $expectedOrder = $order;
            }))
        ;

        $action = new CreateOrderAction($connector);
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

        $request = new CreateOrder($model);

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->exactly(3))
            ->method('apply')
            ->with('POST')
            ->will($this->throwException(new \Klarna_Checkout_ConnectionErrorException()))
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);
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

        $request = new CreateOrder($model);

        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(1))
            ->method('apply')
            ->with('POST')
            ->will($this->throwException(new \Klarna_Checkout_ConnectionErrorException()))
        ;
        $connector
            ->expects($this->at(1))
            ->method('apply')
            ->with('POST')
            ->will($this->returnCallback(function ($method, $order, $options) use (&$expectedOrder) {
                $expectedOrder = $order;
            }))
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertSame($expectedOrder, $request->getOrder());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->createMock(\Klarna_Checkout_ConnectorInterface::class, array(), array(), '', false);
    }
}
