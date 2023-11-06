<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\Api\CreateOrderAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;

class CreateOrderActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Klarna\Checkout\Request\Api\CreateOrder';

    protected $actionClass = 'Payum\Klarna\Checkout\Action\Api\CreateOrderAction';

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array())));
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\CreateOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction'));
    }

    public function testShouldCreateOrderOnExecute()
    {
        $request = new CreateOrder(array());

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
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
            ->expects($this->at(0))
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
            ->expects($this->at(0))
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

        $testCase = $this;
        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('POST')
            ->willReturnCallback(function ($method, $order, $options) use ($testCase, &$expectedOrder) {
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
            ->expects($this->at(0))
            ->method('apply')
            ->with('POST')
            ->willThrowException(new \Klarna_Checkout_ConnectionErrorException())
        ;
        $connector
            ->expects($this->at(1))
            ->method('apply')
            ->with('POST')
            ->willReturnCallback(function ($method, $order, $options) use (&$expectedOrder) {
                $expectedOrder = $order;
            })
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
        return $this->createMock('Klarna_Checkout_ConnectorInterface', array(), array(), '', false);
    }
}
