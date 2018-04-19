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

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\CreateOrderAction');

        $rc->isSubclassOf('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');
    }

    /**
     * @test
     */
    public function shouldCreateOrderOnExecute()
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
            ->expects($this->at(0))
            ->method('apply')
            ->with('POST')
            ->will($this->returnCallback(function ($method, $order, $options) use ($testCase, $model) {
                $testCase->assertInternalType('array', $options);
                $testCase->assertArrayHasKey('data', $options);
                $testCase->assertEquals($model, $options['data']);
            }))
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertInstanceOf('Klarna_Checkout_Order', $request->getOrder());
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
            ->expects($this->at(0))
            ->method('apply')
            ->with('POST')
            ->will($this->returnCallback(function ($method, $order, $options) use ($testCase, $expectedModel) {
                $testCase->assertInternalType('array', $options);
                $testCase->assertArrayHasKey('data', $options);
                $testCase->assertEquals($expectedModel, $options['data']);
            }))
        ;

        $action = new CreateOrderAction($connector);
        $action->setApi($config);

        $action->execute($request);

        $this->assertInstanceOf('Klarna_Checkout_Order', $request->getOrder());
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
     *
     * @expectedException \Klarna_Checkout_ConnectionErrorException
     */
    public function shouldFailedAfterThreeRetriesOnTimeout()
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
            ->expects($this->at(0))
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
        return $this->getMock('Klarna_Checkout_ConnectorInterface', array(), array(), '', false);
    }
}
