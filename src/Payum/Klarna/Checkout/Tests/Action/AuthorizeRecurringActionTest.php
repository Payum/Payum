<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\AuthorizeRecurringAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;

class AuthorizeRecurringActionTest extends GenericActionTest
{
    /**
     * @var Authorize
     */
    protected $requestClass = 'Payum\Core\Request\Authorize';

    /**
     * @var AuthorizeRecurringAction
     */
    protected $actionClass = 'Payum\Klarna\Checkout\Action\AuthorizeRecurringAction';

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass(array(
            'recurring_token' => 'aToken',
        )));
        yield array(new $this->requestClass(array(
            'recurring' => false,
            'recurring_token' => 'aToken',
        )));
        yield array(new $this->requestClass(new \ArrayObject(array(
            'recurring_token' => 'aToken',
        ))));
        yield array(new $this->requestClass(new \ArrayObject(array(
            'recurring' => false,
            'recurring_token' => 'aToken',
        ))));
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array(new $this->requestClass('foo'));
        yield array(new $this->requestClass(new \stdClass()));
        yield array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array())));
        yield array(new $this->requestClass(array(
            'recurring' => true,
            'recurring_token' => 'aToken',
        )));
        yield array(new $this->requestClass(array()));
        yield array(new $this->requestClass(array(
            'recurring' => false,
        )));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(AuthorizeRecurringAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(AuthorizeRecurringAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldAllowSetKlarnaConfigAsApi()
    {
        $action = new AuthorizeRecurringAction();
        $action->setApi(new Config());
    }

    public function testThrowIfNotKlarnaConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Checkout\Config');
        $action = new AuthorizeRecurringAction();
        $action->setApi(new \stdClass());
    }

    public function testShouldDoNothingIfReservationAlreadySet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new AuthorizeRecurringAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize(array(
            'reservation' => 'aReservation',
            'recurring_token' => 'aToken',
        )));
    }

    public function testShouldCreateOrderIfReservationNotSet()
    {
        $orderMock = $this->createOrderMock();
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->will($this->returnValue(array(
                'reservation' => 'theReservation',
            )))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\CreateOrder'))
            ->will($this->returnCallback(function (CreateOrder $request) use ($orderMock) {
                $request->setOrder($orderMock);
            }))
        ;

        $model = new \ArrayObject(array(
            'recurring_token' => 'theToken',
        ));

        $action = new AuthorizeRecurringAction();
        $action->setGateway($gatewayMock);
        $action->setApi(new Config());

        $action->execute(new Authorize($model));

        $this->assertEquals(false, $model['activate']);
        $this->assertEquals('theReservation', $model['reservation']);
        $this->assertEquals('theToken', $model['recurring_token']);
    }

    public function testShouldForceRecurringConfigAndRollbackBackAfterExecution()
    {
        $config = new Config();
        $config->acceptHeader = 'anAcceptHeader';
        $config->contentType = 'aContentType';
        $config->baseUri = Constants::BASE_URI_SANDBOX;
        $config->merchantId = 'aMerchantId';
        $config->secret = 'aSecret';

        $orderMock = $this->createOrderMock();
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->will($this->returnValue(array(
                'reservation' => 'theReservation',
            )))
        ;

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\CreateOrder'))
            ->will($this->returnCallback(function (CreateOrder $request) use ($orderMock, $config, $testCase) {
                $request->setOrder($orderMock);

                $testCase->assertEquals(Constants::ACCEPT_HEADER_RECURRING_ORDER_ACCEPTED_V1, $config->acceptHeader);
                $testCase->assertEquals(Constants::CONTENT_TYPE_RECURRING_ORDER_V1, $config->contentType);
                $testCase->assertEquals('https://checkout.testdrive.klarna.com/checkout/recurring/theToken/orders', $config->baseUri);
                $testCase->assertEquals('aMerchantId', $config->merchantId);
                $testCase->assertEquals('aSecret', $config->secret);
            }))
        ;

        $model = new \ArrayObject(array(
            'recurring_token' => 'theToken',
        ));

        $action = new AuthorizeRecurringAction();
        $action->setGateway($gatewayMock);
        $action->setApi($config);

        $action->execute(new Authorize($model));

        $testCase->assertEquals('anAcceptHeader', $config->acceptHeader);
        $testCase->assertEquals('aContentType', $config->contentType);
        $testCase->assertEquals(Constants::BASE_URI_SANDBOX, $config->baseUri);
        $testCase->assertEquals('aMerchantId', $config->merchantId);
        $testCase->assertEquals('aSecret', $config->secret);
    }

    public function testShouldUseLiveRecurringBaseUriIfConfigHasLiveBaseUri()
    {
        $config = new Config();
        $config->acceptHeader = 'anAcceptHeader';
        $config->contentType = 'aContentType';
        $config->baseUri = Constants::BASE_URI_LIVE;
        $config->merchantId = 'aMerchantId';
        $config->secret = 'aSecret';

        $orderMock = $this->createOrderMock();
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->will($this->returnValue(array(
                'reservation' => 'theReservation',
            )))
        ;

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\CreateOrder'))
            ->will($this->returnCallback(function (CreateOrder $request) use ($orderMock, $config, $testCase) {
                $request->setOrder($orderMock);

                $testCase->assertEquals('https://checkout.klarna.com/checkout/recurring/theToken/orders', $config->baseUri);
            }))
        ;

        $model = new \ArrayObject(array(
            'recurring_token' => 'theToken',
        ));

        $action = new AuthorizeRecurringAction();
        $action->setGateway($gatewayMock);
        $action->setApi($config);

        $action->execute(new Authorize($model));

        $testCase->assertEquals('anAcceptHeader', $config->acceptHeader);
        $testCase->assertEquals('aContentType', $config->contentType);
        $testCase->assertEquals(Constants::BASE_URI_LIVE, $config->baseUri);
        $testCase->assertEquals('aMerchantId', $config->merchantId);
        $testCase->assertEquals('aSecret', $config->secret);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->createMock('Klarna_Checkout_Order', array(), array(), '', false);
    }
}
