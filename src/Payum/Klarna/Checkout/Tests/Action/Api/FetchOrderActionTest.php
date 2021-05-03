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
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\FetchOrderAction');

        $rc->isSubclassOf('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Location has to be provided to fetch an order
     */
    public function throwIfLocationNotSetOnExecute()
    {
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
            ->expects($this->at(0))
            ->method('apply')
            ->with('GET')
            ->will($this->returnCallback(function ($method, $order, $options) use ($testCase, $model) {
                $testCase->assertInternalType('array', $options);
                $testCase->assertArrayHasKey('url', $options);
                $testCase->assertEquals($model['location'], $options['url']);
            }))
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

        $testCase = $this;
        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('GET')
            ->will($this->returnCallback(function ($method, $order, $options) use ($testCase, &$expectedOrder) {
                $expectedOrder = $order;
            }))
        ;

        $action = new FetchOrderAction($connector);
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

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->exactly(3))
            ->method('apply')
            ->with('GET')
            ->will($this->throwException(new \Klarna_Checkout_ConnectionErrorException()))
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
            ->expects($this->at(0))
            ->method('apply')
            ->with('GET')
            ->will($this->throwException(new \Klarna_Checkout_ConnectionErrorException()))
        ;
        $connector
            ->expects($this->at(1))
            ->method('apply')
            ->with('GET')
            ->will($this->returnCallback(function ($method, $order, $options) use (&$expectedOrder) {
                $expectedOrder = $order;
            }))
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
        return $this->getMock('Klarna_Checkout_ConnectorInterface', array(), array(), '', false);
    }
}
