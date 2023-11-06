<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ConfirmOrder;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;

class CaptureActionTest extends GenericActionTest
{
    protected $requestClass = Capture::class;

    protected $actionClass = CaptureAction::class;

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSetZeroGatewayActionAsSell()
    {
        $action = new CaptureAction();
        $action->setGateway($this->createGatewayMock());

        $action->execute($request = new Capture([]));

        $model = $request->getModel();
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $model);
        $this->assertSame(Api::PAYMENTACTION_SALE, $model['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    public function testShouldForcePaymentActionSale()
    {
        $action = new CaptureAction();
        $action->setGateway($this->createGatewayMock());

        $action->execute($request = new Capture([
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'FooBarBaz',
        ]));

        $model = $request->getModel();
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $model);
        $this->assertSame(Api::PAYMENTACTION_SALE, $model['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    public function testShouldRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenNotSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(SetExpressCheckout::class))
        ;
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;
        $gatewayMock
            ->expects($this->at(3))
            ->method('execute')
            ->with($this->isInstanceOf(AuthorizeToken::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture([]));
    }

    public function testShouldRequestAuthorizeActionIfPayerIdNotSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf(AuthorizeToken::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture([
            'TOKEN' => 'aToken',
            'PAYERID' => null,
        ]));
    }

    public function testShouldNotRequestAuthorizeActionIfPayerIdSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture([
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
        ]));
    }

    public function testShouldNotExecuteAnythingIfSetExpressCheckoutActionFails()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(SetExpressCheckout::class))
            ->willReturnCallback(function (SetExpressCheckout $request) {
                $model = $request->getModel();

                $model['L_ERRORCODE0'] = 'aCode';
            })
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture([]));
    }

    public function testShouldSetTokenTargetUrlAsReturnUrlIfCapturePassedWithToken()
    {
        $testCase = $this;

        $expectedTargetUrl = 'theTargetUrl';

        $token = new Token();
        $token->setTargetUrl($expectedTargetUrl);
        $token->setDetails(array());

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(SetExpressCheckout::class))
            ->willReturnCallback(function ($request) use ($testCase, $expectedTargetUrl) {
                $model = $request->getModel();

                $testCase->assertSame($expectedTargetUrl, $model['RETURNURL']);
            })
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture($token);
        $request->setModel(array());

        $action->execute($request);
    }

    public function testShouldSetTokenTargetUrlAsCancelUrlIfCapturePassedWithToken()
    {
        $testCase = $this;

        $cancelUrl = 'http://thecancelurl/';
        $expectedCancelUrl = $cancelUrl.'?cancelled=1';

        $token = new Token();
        $token->setTargetUrl($cancelUrl);
        $token->setDetails(array());

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(SetExpressCheckout::class))
            ->willReturnCallback(function ($request) use ($testCase, $expectedCancelUrl) {
                $model = $request->getModel();

                $testCase->assertSame($expectedCancelUrl, $model['CANCELURL']);
            })
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture($token);
        $request->setModel(array());

        $action->execute($request);
    }

    public function testShouldNotLooseExistingGetParamWhenSettingTargetUrlAsCancelUrl()
    {
        $testCase = $this;

        $cancelUrl = 'http://thecancelurl/?existingGetParam=testValue';
        $expectedCancelUrl = $cancelUrl.'&cancelled=1';

        $token = new Token();
        $token->setTargetUrl($cancelUrl);
        $token->setDetails(array());

        $gatewayMock = $this->createGatewayMock();

        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(SetExpressCheckout::class))
            ->willReturnCallback(function ($request) use ($testCase, $expectedCancelUrl) {
                $model = $request->getModel();

                $testCase->assertSame($expectedCancelUrl, $model['CANCELURL']);
            })
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture($token);
        $request->setModel(array());

        $action->execute($request);
    }

    public function testShouldNotRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'TOKEN' => 'aToken',
        )));
    }

    public function testShouldRequestDoExpressCheckoutGatewayActionIfCheckoutStatusNotInitiatedAndPayerIdSetInModelAndUserActionCommit()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf(DoExpressCheckoutPayment::class))
        ;
        $gatewayMock
            ->expects($this->at(3))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'AUTHORIZE_TOKEN_USERACTION' => Api::USERACTION_COMMIT,
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 5,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        )));
    }

    public function testShouldRequestConfirmOrderActionIfCheckoutStatusNotInitiatedAndPayerIdSetInModelAndUserActionNotCommit()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf(ConfirmOrder::class))
        ;
        $gatewayMock
            ->expects($this->at(3))
            ->method('execute')
            ->with($this->isInstanceOf(DoExpressCheckoutPayment::class))
        ;
        $gatewayMock
            ->expects($this->at(4))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'AUTHORIZE_TOKEN_USERACTION' => '',
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 5,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        )));
    }

    public function testShouldRequestAuthorizeTokenIfPayerIdNotSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf(AuthorizeToken::class))
        ;
        $gatewayMock
            ->expects($this->at(3))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'TOKEN' => 'aToken',
            'PAYERID' => null,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        )));
    }

    public function testShouldNotRequestDoExpressCheckoutGatewayActionIfCheckoutStatusOtherThenNotInitiatedSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'anId',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS,
        )));
    }

    public function testShouldNotRequestDoExpressCheckoutGatewayActionIfAmountZero()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'TOKEN' => 'aToken',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 0,
        )));
    }

    public function testShouldAddNotifyUrlIfTokenFactorySetAndCaptureTokenPassed()
    {
        $details = new \ArrayObject(array(
            'foo' => 'fooVal',
        ));

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

        $action = new CaptureAction();
        $action->setGateway($this->createGatewayMock());
        $action->setGenericTokenFactory($tokenFactoryMock);

        $request = new Capture($captureToken);
        $request->setModel($details);

        $action->execute($request);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
        $this->assertSame('theNotifyUrl', $details['PAYMENTREQUEST_0_NOTIFYURL']);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }

    public function testShouldNotAddNotifyUrlIfAlreadySet()
    {
        $details = new \ArrayObject(array(
            'PAYMENTREQUEST_0_NOTIFYURL' => 'alreadySetUrl',
        ));

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setDetails($details);

        $tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createNotifyToken')
        ;

        $action = new CaptureAction();
        $action->setGateway($this->createGatewayMock());
        $action->setGenericTokenFactory($tokenFactoryMock);

        $request = new Capture($captureToken);
        $request->setModel($details);

        $action->execute($request);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
        $this->assertSame('alreadySetUrl', $details['PAYMENTREQUEST_0_NOTIFYURL']);
    }

    public function testShouldNotAddNotifyUrlIfPaypalTokenAlreadySet()
    {
        $details = new \ArrayObject(array(
            'TOKEN' => 'foo',
        ));

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setDetails($details);

        $tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createNotifyToken')
        ;

        $action = new CaptureAction();
        $action->setGateway($this->createGatewayMock());
        $action->setGenericTokenFactory($tokenFactoryMock);

        $request = new Capture($captureToken);
        $request->setModel($details);

        $action->execute($request);

        $this->assertArrayNotHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
    }

    public function testShouldNotAddNotifyUrlIfTokenFactoryNotSet()
    {
        $details = new \ArrayObject(array(
        ));

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setDetails($details);

        $action = new CaptureAction();
        $action->setGateway($this->createGatewayMock());

        $request = new Capture($captureToken);
        $request->setModel($details);

        $action->execute($request);

        $this->assertArrayNotHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
    }

    public function testShouldNotAddNotifyUrlIfCaptureTokenNotSet()
    {
        $details = new \ArrayObject();

        $tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createNotifyToken')
        ;

        $action = new CaptureAction();
        $action->setGateway($this->createGatewayMock());
        $action->setGenericTokenFactory($tokenFactoryMock);

        $action->execute(new Capture($details));

        $this->assertNotEmpty($details);

        $this->assertArrayNotHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
