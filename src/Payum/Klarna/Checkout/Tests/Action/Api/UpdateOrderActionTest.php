<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\Api\UpdateOrderAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;

class UpdateOrderActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Klarna\Checkout\Request\Api\UpdateOrder';

    protected $actionClass = 'Payum\Klarna\Checkout\Action\Api\UpdateOrderAction';

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
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\UpdateOrderAction');

        $rc->isSubclassOf('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');
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
                    array('bar'),
                ),
            ),
        );

        $request = new UpdateOrder($model);

        $testCase = $this;

        $connector = $this->createConnectorMock();
        $connector
            ->expects($this->at(0))
            ->method('apply')
            ->with('POST')
            ->will($this->returnCallback(function ($method, $order, $options) use ($testCase, $model) {
                $testCase->assertInternalType('array', $options);
                $testCase->assertArrayHasKey('data', $options);
                $testCase->assertEquals(array('cart' => $model['cart']), $options['data']);
            }))
        ;

        $action = new UpdateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request);

        $this->assertInstanceOf('Klarna_Checkout_Order', $request->getOrder());
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
            ->with('POST')
            ->will($this->throwException(new \Klarna_Checkout_ConnectionErrorException()))
        ;

        $action = new UpdateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute(new UpdateOrder($model));
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
            ->will($this->returnCallback(function ($method, $order, $options) use ($model) {
                $this->assertInternalType('array', $options);
                $this->assertArrayHasKey('data', $options);
                $this->assertEquals(array('cart' => $model['cart']), $options['data']);
            }))
        ;

        $action = new UpdateOrderAction($connector);
        $action->setApi(new Config());

        $action->execute($request = new UpdateOrder($model));

        $this->assertInstanceOf('Klarna_Checkout_Order', $request->getOrder());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->getMock('Klarna_Checkout_ConnectorInterface', array(), array(), '', false);
    }
}
