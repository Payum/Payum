<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
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

class AutorizeActionTest extends GenericActionTest
{
    protected $requestClass = Authorize::class;

    protected $actionClass = AuthorizeAction::class;

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSetZeroGatewayActionAsSell()
    {
        $action = new AuthorizeAction();
        $action->setGateway($this->createGatewayMock());

        $action->execute($request = new Authorize([]));

        $model = $request->getModel();
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $model);
        $this->assertEquals(Api::PAYMENTACTION_AUTHORIZATION, $model['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    /**
     * @test
     */
    public function shouldForcePaymentActionAuthorization()
    {
        $action = new AuthorizeAction();
        $action->setGateway($this->createGatewayMock());

        $action->execute($request = new Authorize([
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'FooBarBaz',
        ]));

        $model = $request->getModel();
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $model);
        $this->assertEquals(Api::PAYMENTACTION_AUTHORIZATION, $model['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    /**
     * @test
     */
    public function shouldRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenNotSetInModel()
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

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([]));
    }

    /**
     * @test
     */
    public function shouldRequestAuthorizeActionIfPayerIdNotSetInModel()
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

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'TOKEN' => 'aToken',
            'PAYERID' => null,
        ]));
    }

    /**
     * @test
     */
    public function shouldNotRequestAuthorizeActionIfPayerIdSetInModel()
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

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
        ]));
    }

    /**
     * @test
     */
    public function shouldNotExecuteAnythingIfSetExpressCheckoutActionFails()
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
            ->will($this->returnCallback(function (SetExpressCheckout $request) {
                $model = $request->getModel();

                $model['L_ERRORCODE0'] = 'aCode';
            }))
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize([]));
    }

    /**
     * @test
     */
    public function shouldSetTokenTargetUrlAsReturnUrlIfCapturePassedWithToken()
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
            ->will($this->returnCallback(function ($request) use ($testCase, $expectedTargetUrl) {
                $model = $request->getModel();

                $testCase->assertEquals($expectedTargetUrl, $model['RETURNURL']);
            }))
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize($token);
        $request->setModel(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldSetTokenTargetUrlAsCancelUrlIfCapturePassedWithToken()
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
            ->will($this->returnCallback(function ($request) use ($testCase, $expectedCancelUrl) {
                $model = $request->getModel();

                $testCase->assertEquals($expectedCancelUrl, $model['CANCELURL']);
            }))
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize($token);
        $request->setModel(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldNotLooseExistingGetParamWhenSettingTargetUrlAsCancelUrl()
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
            ->will($this->returnCallback(function ($request) use ($testCase, $expectedCancelUrl) {
                $model = $request->getModel();

                $testCase->assertEquals($expectedCancelUrl, $model['CANCELURL']);
            }))
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize($token);
        $request->setModel(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldNotRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenSetInModel()
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

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize(array(
            'TOKEN' => 'aToken',
        )));
    }

    /**
     * @test
     */
    public function shouldRequestDoExpressCheckoutGatewayActionIfCheckoutStatusNotInitiatedAndPayerIdSetInModelAndUserActionCommit()
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

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize(array(
            'AUTHORIZE_TOKEN_USERACTION' => Api::USERACTION_COMMIT,
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 5,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        )));
    }

    /**
     * @test
     */
    public function shouldRequestConfirmOrderActionIfCheckoutStatusNotInitiatedAndPayerIdSetInModelAndUserActionNotCommit()
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

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize(array(
            'AUTHORIZE_TOKEN_USERACTION' => '',
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 5,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        )));
    }

    /**
     * @test
     */
    public function shouldRequestAuthorizeTokenIfPayerIdNotSetInModel()
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

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize(array(
            'TOKEN' => 'aToken',
            'PAYERID' => null,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        )));
    }

    /**
     * @test
     */
    public function shouldNotRequestDoExpressCheckoutGatewayActionIfCheckoutStatusOtherThenNotInitiatedSetInModel()
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

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'anId',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS,
        )));
    }

    /**
     * @test
     */
    public function shouldNotRequestDoExpressCheckoutGatewayActionIfAmountZero()
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

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Authorize(array(
            'TOKEN' => 'aToken',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 0,
        )));
    }

    /**
     * @test
     */
    public function shouldAddNotifyUrlIfTokenFactorySetAndCaptureTokenPassed()
    {
        $details = new \ArrayObject(array(
            'foo' => 'fooVal',
        ));

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setDetails($details);

        $notifyToken = new Token();
        $notifyToken->setTargetUrl('theNotifyUrl');

        $tokenFactoryMock = $this->getMock(GenericTokenFactoryInterface::class);
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with('theGatewayName', $this->identicalTo($details))
            ->will($this->returnValue($notifyToken))
        ;

        $action = new AuthorizeAction();
        $action->setGateway($this->createGatewayMock());
        $action->setGenericTokenFactory($tokenFactoryMock);

        $request = new Authorize($captureToken);
        $request->setModel($details);

        $action->execute($request);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
        $this->assertEquals('theNotifyUrl', $details['PAYMENTREQUEST_0_NOTIFYURL']);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }

    /**
     * @test
     */
    public function shouldNotAddNotifyUrlIfAlreadySet()
    {
        $details = new \ArrayObject(array(
            'PAYMENTREQUEST_0_NOTIFYURL' => 'alreadySetUrl',
        ));

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setDetails($details);

        $tokenFactoryMock = $this->getMock(GenericTokenFactoryInterface::class);
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
        $this->assertEquals('alreadySetUrl', $details['PAYMENTREQUEST_0_NOTIFYURL']);
    }

    /**
     * @test
     */
    public function shouldNotAddNotifyUrlIfPaypalTokenAlreadySet()
    {
        $details = new \ArrayObject(array(
            'TOKEN' => 'foo',
        ));

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setDetails($details);

        $tokenFactoryMock = $this->getMock(GenericTokenFactoryInterface::class);
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

    /**
     * @test
     */
    public function shouldNotAddNotifyUrlIfTokenFactoryNotSet()
    {
        $details = new \ArrayObject(array(
        ));

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

    /**
     * @test
     */
    public function shouldNotAddNotifyUrlIfCaptureTokenNotSet()
    {
        $details = new \ArrayObject();

        $tokenFactoryMock = $this->getMock(GenericTokenFactoryInterface::class);
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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
