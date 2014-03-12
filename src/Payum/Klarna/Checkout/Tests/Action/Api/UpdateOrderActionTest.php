<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Payum\Klarna\Checkout\Action\Api\UpdateOrderAction;
use Payum\Klarna\Checkout\Request\Api\UpdateOrderRequest;

class UpdateOrderActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\UpdateOrderAction');

        $rc->isSubclassOf('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new UpdateOrderAction;
    }

    /**
     * @test
     */
    public function shouldSupportUpdateOrderRequest()
    {
        $action = new UpdateOrderAction;

        $this->assertTrue($action->supports(new UpdateOrderRequest(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotUpdateOrderRequest()
    {
        $action = new UpdateOrderAction;

        $this->assertFalse($action->supports(new \stdClass));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new UpdateOrderAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldUpdateOrderIfModelHasCartItemsSetOnExecute()
    {
        $model = array(
            'location' => 'theLocation',
            'cart' => array(
                'items' => array(
                    array('foo'),
                    array('bar')
                )
            )
        );

        $request = new UpdateOrderRequest($model);

        $testCase = $this;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('POST')
            ->will($this->returnCallback(function($method, $order, $options) use ($testCase, $model) {
                $testCase->assertInternalType('array', $options);
                $testCase->assertArrayHasKey('data', $options);
                $testCase->assertEquals(array('cart' => $model['cart']), $options['data']);
            }))
        ;
        $connector
            ->expects($this->at(1))
            ->method('apply')
            ->with('GET')
            ->will($this->returnCallback(function($method, $order, $options) use ($testCase, $model) {
                $testCase->assertInternalType('array', $options);
                $testCase->assertArrayHasKey('url', $options);
                $testCase->assertEquals($model['location'], $options['url']);
            }))
        ;

        $action = new UpdateOrderAction();
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

        $request = new UpdateOrderRequest($model);

        $testCase = $this;
        $expectedOrder = null;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(1))
            ->method('apply')
            ->with('GET')
            ->will($this->returnCallback(function($method, $order, $options) use ($testCase, &$expectedOrder) {
                $expectedOrder = $order;
            }))
        ;

        $action = new UpdateOrderAction();
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