<?php

namespace Payum\Payex\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Payex\Action\Api\InitializeOrderAction;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\InitializeOrder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class InitializeOrderActionTest extends TestCase
{
    protected $requiredFields = [
        'price' => 1000,
        'priceArgList' => '',
        'vat' => 0,
        'currency' => 'NOK',
        'orderId' => 123,
        'productNumber' => 123,
        'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
        'view' => OrderApi::VIEW_CREDITCARD,
        'description' => 'a description',
        'additionalValues' => '',
        'returnUrl' => 'http://example.com/a_return_url',
        'cancelUrl' => 'http://example.com/a_cancel_url',
        'clientIPAddress' => '127.0.0.1',
        'clientIdentifier' => 'USER-AGENT=cli-php',
        'agreementRef' => '',
        'clientLanguage' => 'en-US',
    ];

    public function provideRequiredFields()
    {
        $fields = [];

        foreach ($this->requiredFields as $name => $value) {
            $fields[] = [$name];
        }

        return $fields;
    }

    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(InitializeOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new ReflectionClass(InitializeOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotOrderApiAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\OrderApi');
        $action = new InitializeOrderAction();

        $action->setApi(new stdClass());
    }

    public function testShouldSupportInitializeOrderRequestWithArrayAccessAsModel()
    {
        $action = new InitializeOrderAction();

        $this->assertTrue($action->supports(new InitializeOrder($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotInitializeOrderRequest()
    {
        $action = new InitializeOrderAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportInitializeOrderRequestWithNotArrayAccessModel()
    {
        $action = new InitializeOrderAction();

        $this->assertFalse($action->supports(new InitializeOrder(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new InitializeOrderAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    public function testThrowIfTryInitializeAlreadyInitializedOrder()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The order has already been initialized.');
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('initialize')
        ;

        $action = new InitializeOrderAction();
        $action->setApi($apiMock);

        $action->execute(new InitializeOrder([
            'orderRef' => 'aRef',
        ]));
    }

    #[DataProvider('provideRequiredFields')]
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        $this->expectException(LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new InitializeOrderAction();

        $action->execute(new InitializeOrder($this->requiredFields));
    }

    public function testShouldInitializePayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('initialize')
            ->with($this->requiredFields)
            ->willReturn([
                'orderRef' => 'theRef',
            ]);

        $action = new InitializeOrderAction();
        $action->setApi($apiMock);

        $request = new InitializeOrder($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theRef', $model['orderRef']);
    }

    public function testShouldThrowHttpRedirectReplyIfRedirectUrlReturnedInResponse()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('initialize')
            ->with($this->requiredFields)
            ->willReturn([
                'redirectUrl' => 'http://example.com/theUrl',
            ]);

        $action = new InitializeOrderAction();
        $action->setApi($apiMock);

        $request = new InitializeOrder($this->requiredFields);

        try {
            $action->execute($request);
        } catch (HttpRedirect $reply) {
            $this->assertSame('http://example.com/theUrl', $reply->getUrl());

            return;
        }

        $this->fail('The redirect url reply was expected to be thrown.');
    }

    /**
     * @return MockObject|OrderApi
     */
    protected function createApiMock()
    {
        return $this->createMock(OrderApi::class);
    }
}
