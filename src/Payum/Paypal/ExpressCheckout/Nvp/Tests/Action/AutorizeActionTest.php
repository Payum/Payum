<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\AuthorizeAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ConfirmOrder;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class AutorizeActionTest extends GenericActionTest
{
    /**
     * @var class-string<Authorize>
     */
    protected $requestClass = Authorize::class;

    /**
     * @var class-string<AuthorizeAction>
     */
    protected $actionClass = AuthorizeAction::class;

    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSetZeroGatewayActionAsSell(): void
    {
        $action = new AuthorizeAction();
        $action->setGateway($this->createGatewayMock());

        $action->execute($request = new Authorize([]));

        $model = $request->getModel();
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $model);
        $this->assertSame(Api::PAYMENTACTION_AUTHORIZATION, $model['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    public function testShouldForcePaymentActionAuthorization(): void
    {
        $action = new AuthorizeAction();
        $action->setGateway($this->createGatewayMock());

        $action->execute($request = new Authorize([
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'FooBarBaz',
        ]));

        $model = $request->getModel();
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $model);
        $this->assertSame(Api::PAYMENTACTION_AUTHORIZATION, $model['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    public function testShouldRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenNotSetInModel(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(4))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(SetExpressCheckout::class)],
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(AuthorizeToken::class)]
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([]));
    }

    public function testShouldRequestAuthorizeActionIfPayerIdNotSetInModel(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(AuthorizeToken::class)]
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'TOKEN' => 'aToken',
            'PAYERID' => null,
        ]));
    }

    public function testShouldNotRequestAuthorizeActionIfPayerIdSetInModel(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(Sync::class)]
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
        ]));
    }

    public function testShouldNotExecuteAnythingIfSetExpressCheckoutActionFails(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(SetExpressCheckout::class)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->returnCallback(function (SetExpressCheckout $request): void {
                    $model = $request->getModel();

                    $model['L_ERRORCODE0'] = 'aCode';
                })
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([]));
    }

    public function testShouldSetTokenTargetUrlAsReturnUrlIfCapturePassedWithToken(): void
    {
        $testCase = $this;

        $expectedTargetUrl = 'theTargetUrl';

        $token = new Token();
        $token->setTargetUrl($expectedTargetUrl);
        $token->setDetails([]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(SetExpressCheckout::class)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->returnCallback(function ($request) use ($testCase, $expectedTargetUrl): void {
                    $model = $request->getModel();

                    $testCase->assertSame($expectedTargetUrl, $model['RETURNURL']);
                })
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize($token);
        $request->setModel([]);

        $action->execute($request);
    }

    public function testShouldSetTokenTargetUrlAsCancelUrlIfCapturePassedWithToken(): void
    {
        $testCase = $this;

        $cancelUrl = 'http://thecancelurl/';
        $expectedCancelUrl = $cancelUrl . '?cancelled=1';

        $token = new Token();
        $token->setTargetUrl($cancelUrl);
        $token->setDetails([]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(SetExpressCheckout::class)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->returnCallback(function ($request) use ($testCase, $expectedCancelUrl): void {
                    $model = $request->getModel();

                    $testCase->assertSame($expectedCancelUrl, $model['CANCELURL']);
                })
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize($token);
        $request->setModel([]);

        $action->execute($request);
    }

    public function testShouldNotLooseExistingGetParamWhenSettingTargetUrlAsCancelUrl(): void
    {
        $testCase = $this;

        $cancelUrl = 'http://thecancelurl/?existingGetParam=testValue';
        $expectedCancelUrl = $cancelUrl . '&cancelled=1';

        $token = new Token();
        $token->setTargetUrl($cancelUrl);
        $token->setDetails([]);

        $gatewayMock = $this->createGatewayMock();

        $gatewayMock
            ->expects($this->atLeastOnce())
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(SetExpressCheckout::class)]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                $this->returnCallback(function ($request) use ($testCase, $expectedCancelUrl): void {
                    $model = $request->getModel();

                    $testCase->assertSame($expectedCancelUrl, $model['CANCELURL']);
                })
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize($token);
        $request->setModel([]);

        $action->execute($request);
    }

    public function testShouldNotRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenSetInModel(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(Sync::class)]
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'TOKEN' => 'aToken',
        ]));
    }

    public function testShouldRequestDoExpressCheckoutGatewayActionIfCheckoutStatusNotInitiatedAndPayerIdSetInModelAndUserActionCommit(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(4))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(DoExpressCheckoutPayment::class)],
                [$this->isInstanceOf(Sync::class)]
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'AUTHORIZE_TOKEN_USERACTION' => Api::USERACTION_COMMIT,
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 5,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        ]));
    }

    public function testShouldRequestConfirmOrderActionIfCheckoutStatusNotInitiatedAndPayerIdSetInModelAndUserActionNotCommit(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(5))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(ConfirmOrder::class)],
                [$this->isInstanceOf(DoExpressCheckoutPayment::class)],
                [$this->isInstanceOf(Sync::class)]
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'AUTHORIZE_TOKEN_USERACTION' => '',
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 5,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        ]));
    }

    public function testShouldRequestAuthorizeTokenIfPayerIdNotSetInModel(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(4))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(AuthorizeToken::class)],
                [$this->isInstanceOf(Sync::class)]
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'TOKEN' => 'aToken',
            'PAYERID' => null,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        ]));
    }

    public function testShouldNotRequestDoExpressCheckoutGatewayActionIfCheckoutStatusOtherThenNotInitiatedSetInModel(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(Sync::class)]
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'TOKEN' => 'aToken',
            'PAYERID' => 'anId',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS,
        ]));
    }

    public function testShouldNotRequestDoExpressCheckoutGatewayActionIfAmountZero(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(Sync::class)]
            )
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'TOKEN' => 'aToken',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 0,
        ]));
    }

    public function testShouldAddNotifyUrlIfTokenFactorySetAndCaptureTokenPassed(): void
    {
        $details = new ArrayObject([
            'foo' => 'fooVal',
        ]);

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setDetails($details);

        $notifyToken = new Token();
        $notifyToken->setTargetUrl('theNotifyUrl');

        $tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with('theGatewayName', $this->identicalTo($details))
            ->willReturn($notifyToken)
        ;

        $action = new AuthorizeAction();
        $action->setGateway($this->createGatewayMock());
        $action->setGenericTokenFactory($tokenFactoryMock);

        $request = new Authorize($captureToken);
        $request->setModel($details);

        $action->execute($request);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
        $this->assertSame('theNotifyUrl', $details['PAYMENTREQUEST_0_NOTIFYURL']);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }

    public function testShouldNotAddNotifyUrlIfAlreadySet(): void
    {
        $details = new ArrayObject([
            'PAYMENTREQUEST_0_NOTIFYURL' => 'alreadySetUrl',
        ]);

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setDetails($details);

        $tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createNotifyToken')
        ;

        $action = new AuthorizeAction();
        $action->setGateway($this->createGatewayMock());
        $action->setGenericTokenFactory($tokenFactoryMock);

        $request = new Authorize($captureToken);
        $request->setModel($details);

        $action->execute($request);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
        $this->assertSame('alreadySetUrl', $details['PAYMENTREQUEST_0_NOTIFYURL']);
    }

    public function testShouldNotAddNotifyUrlIfPaypalTokenAlreadySet(): void
    {
        $details = new ArrayObject([
            'TOKEN' => 'foo',
        ]);

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setDetails($details);

        $tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createNotifyToken')
        ;

        $action = new AuthorizeAction();
        $action->setGateway($this->createGatewayMock());
        $action->setGenericTokenFactory($tokenFactoryMock);

        $request = new Authorize($captureToken);
        $request->setModel($details);

        $action->execute($request);

        $this->assertArrayNotHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
    }

    public function testShouldNotAddNotifyUrlIfTokenFactoryNotSet(): void
    {
        $details = new ArrayObject([
        ]);

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setDetails($details);

        $action = new AuthorizeAction();
        $action->setGateway($this->createGatewayMock());

        $request = new Authorize($captureToken);
        $request->setModel($details);

        $action->execute($request);

        $this->assertArrayNotHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
    }

    public function testShouldNotAddNotifyUrlIfCaptureTokenNotSet(): void
    {
        $details = new ArrayObject();

        $tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createNotifyToken')
        ;

        $action = new AuthorizeAction();
        $action->setGateway($this->createGatewayMock());
        $action->setGenericTokenFactory($tokenFactoryMock);

        $action->execute(new Authorize($details));

        $this->assertNotEmpty($details);

        $this->assertArrayNotHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
