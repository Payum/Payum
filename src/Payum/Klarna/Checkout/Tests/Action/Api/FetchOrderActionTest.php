<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Payum\Klarna\Checkout\Action\Api\FetchOrderAction;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\FetchOrderRequest;

class FetchOrderActionTest extends \PHPUnit_Framework_TestCase
{
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
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new FetchOrderAction;
    }

    /**
     * @test
     */
    public function shouldSupportFetchOrderRequest()
    {
        $action = new FetchOrderAction;

        $this->assertTrue($action->supports(new FetchOrderRequest(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotFetchOrderRequest()
    {
        $action = new FetchOrderAction;

        $this->assertFalse($action->supports(new \stdClass));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new FetchOrderAction();

        $action->execute(new \stdClass());
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

        $action->execute(new FetchOrderRequest(array()));
    }

    /**
     * @test
     */
    public function shouldFetchOrderWhenLocationSetOnExecute()
    {
        $model = array(
            'location' => 'theKlarnaOrderLocation'
        );

        $request = new FetchOrderRequest($model);

        $connector = $this->createConnectorMock();

        $testCase = $this;

        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('GET')
            ->will($this->returnCallback(function($method, $order, $options) use ($testCase, $model) {
                $testCase->assertInternalType('array', $options);
                $testCase->assertArrayHasKey('url', $options);
                $testCase->assertEquals($model['location'], $options['url']);
            }))
        ;

        $action = new FetchOrderAction();
        $action->setApi($connector);

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
                    array('bar')
                )
            )
        );

        $request = new FetchOrderRequest($model);

        $testCase = $this;
        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('GET')
            ->will($this->returnCallback(function($method, $order, $options) use ($testCase, &$expectedOrder) {
                $expectedOrder = $order;
            }))
        ;

        $action = new FetchOrderAction();
        $action->setApi($connector);

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