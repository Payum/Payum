<?php

namespace Payum\Klarna\Checkout\Tests\Action;

use ArrayObject;
use Iterator;
use Klarna_Checkout_Order;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Generic;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\AuthorizeRecurringAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use stdClass;

class AuthorizeRecurringActionTest extends GenericActionTest
{
    /**
     * @var class-string<Authorize>
     */
    protected $requestClass = Authorize::class;

    /**
     * @var class-string<AuthorizeRecurringAction>
     */
    protected $actionClass = AuthorizeRecurringAction::class;

    /**
     * @return \Iterator<Authorize[]>
     */
    public function provideSupportedRequests(): Iterator
    {
        yield [new $this->requestClass([
            'recurring_token' => 'aToken',
        ])];
        yield [new $this->requestClass([
            'recurring' => false,
            'recurring_token' => 'aToken',
        ])];
        yield [new $this->requestClass(new ArrayObject([
            'recurring_token' => 'aToken',
        ]))];
        yield [new $this->requestClass(new ArrayObject([
            'recurring' => false,
            'recurring_token' => 'aToken',
        ]))];
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [new $this->requestClass('foo')];
        yield [new $this->requestClass(new stdClass())];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
        yield [new $this->requestClass([
            'recurring' => true,
            'recurring_token' => 'aToken',
        ])];
        yield [new $this->requestClass([])];
        yield [new $this->requestClass([
            'recurring' => false,
        ])];
    }

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(AuthorizeRecurringAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldImplementsApiAwareInterface(): void
    {
        $rc = new ReflectionClass(AuthorizeRecurringAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testThrowIfNotKlarnaConfigGivenAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Checkout\Config');
        $action = new AuthorizeRecurringAction();
        $action->setApi(new stdClass());
    }

    public function testShouldDoNothingIfReservationAlreadySet(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new AuthorizeRecurringAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'reservation' => 'aReservation',
            'recurring_token' => 'aToken',
        ]));
    }

    public function testShouldCreateOrderIfReservationNotSet(): void
    {
        $orderMock = $this->createOrderMock();
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->willReturn([
                'reservation' => 'theReservation',
            ])
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateOrder::class))
            ->willReturnCallback(function (CreateOrder $request) use ($orderMock): void {
                $request->setOrder($orderMock);
            })
        ;

        $model = new ArrayObject([
            'recurring_token' => 'theToken',
        ]);

        $action = new AuthorizeRecurringAction();
        $action->setGateway($gatewayMock);
        $action->setApi(new Config());

        $action->execute(new Authorize($model));

        $this->assertEquals(false, $model['activate']);
        $this->assertSame('theReservation', $model['reservation']);
        $this->assertSame('theToken', $model['recurring_token']);
    }

    public function testShouldForceRecurringConfigAndRollbackBackAfterExecution(): void
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
            ->willReturn([
                'reservation' => 'theReservation',
            ])
        ;

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateOrder::class))
            ->willReturnCallback(function (CreateOrder $request) use ($orderMock, $config, $testCase): void {
                $request->setOrder($orderMock);

                $testCase->assertSame(Constants::ACCEPT_HEADER_RECURRING_ORDER_ACCEPTED_V1, $config->acceptHeader);
                $testCase->assertSame(Constants::CONTENT_TYPE_RECURRING_ORDER_V1, $config->contentType);
                $testCase->assertSame('https://checkout.testdrive.klarna.com/checkout/recurring/theToken/orders', $config->baseUri);
                $testCase->assertSame('aMerchantId', $config->merchantId);
                $testCase->assertSame('aSecret', $config->secret);
            })
        ;

        $model = new ArrayObject([
            'recurring_token' => 'theToken',
        ]);

        $action = new AuthorizeRecurringAction();
        $action->setGateway($gatewayMock);
        $action->setApi($config);

        $action->execute(new Authorize($model));

        $testCase->assertSame('anAcceptHeader', $config->acceptHeader);
        $testCase->assertSame('aContentType', $config->contentType);
        $testCase->assertSame(Constants::BASE_URI_SANDBOX, $config->baseUri);
        $testCase->assertSame('aMerchantId', $config->merchantId);
        $testCase->assertSame('aSecret', $config->secret);
    }

    public function testShouldUseLiveRecurringBaseUriIfConfigHasLiveBaseUri(): void
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
            ->willReturn([
                'reservation' => 'theReservation',
            ])
        ;

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateOrder::class))
            ->willReturnCallback(function (CreateOrder $request) use ($orderMock, $config, $testCase): void {
                $request->setOrder($orderMock);

                $testCase->assertSame('https://checkout.klarna.com/checkout/recurring/theToken/orders', $config->baseUri);
            })
        ;

        $model = new ArrayObject([
            'recurring_token' => 'theToken',
        ]);

        $action = new AuthorizeRecurringAction();
        $action->setGateway($gatewayMock);
        $action->setApi($config);

        $action->execute(new Authorize($model));

        $testCase->assertSame('anAcceptHeader', $config->acceptHeader);
        $testCase->assertSame('aContentType', $config->contentType);
        $testCase->assertSame(Constants::BASE_URI_LIVE, $config->baseUri);
        $testCase->assertSame('aMerchantId', $config->merchantId);
        $testCase->assertSame('aSecret', $config->secret);
    }

    /**
     * @return MockObject|Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->createMock(Klarna_Checkout_Order::class);
    }
}
