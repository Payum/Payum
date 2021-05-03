<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Klarna\Checkout\Action\AuthorizeAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;

class AuthorizeActionTest extends \PHPUnit_Framework_TestCase
{
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
    public function shouldImplementsGenericTokenFactoryAwareInterface()
    {
        $rc = new \ReflectionClass(AuthorizeAction::class);
        $rc = new \ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(GenericTokenFactoryAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AuthorizeAction('aTemplate');
    }

    /**
     * @test
     */
    public function shouldSupportAuthorizeWithArrayAsModel()
    {
        $action = new AuthorizeAction('aTemplate');

        $this->assertTrue($action->supports(new Authorize(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorize()
    {
        $action = new AuthorizeAction('aTemplate');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportAuthorizeWithNotArrayAccessModel()
    {
        $action = new AuthorizeAction('aTemplate');

        $this->assertFalse($action->supports(new Authorize(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new AuthorizeAction('aTemplate');

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Reply\HttpResponse
     */
    public function shouldSubExecuteSyncIfModelHasLocationSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
        ;

        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($gatewayMock);
        $action->setApi(new Config());

        $action->execute(new Authorize(array(
            'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
            'location' => 'aLocation',
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'push_uri' => 'thePushUri',
                'checkout_uri' => 'theCheckoutUri',
                'terms_uri' => 'theTermsUri',
            ]
        )));
    }

    /**
     * @test
     */
    public function shouldSubExecuteCreateOrderRequestIfStatusAndLocationNotSet()
    {
        $orderMock = $this->createOrderMock();
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->will($this->returnValue(array(
                'foo' => 'fooVal',
                'bar' => 'barVal',
            )))
        ;
        $orderMock
            ->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue('theLocation'))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(CreateOrder::class))
            ->will($this->returnCallback(function (CreateOrder $request) use ($orderMock) {
                $request->setOrder($orderMock);
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($gatewayMock);
        $action->setApi(new Config());

        $model = new \ArrayObject([
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'push_uri' => 'thePushUri',
                'checkout_uri' => 'theCheckoutUri',
                'terms_uri' => 'theTermsUri',
            ]
        ]);

        $action->execute(new Authorize($model));

        $this->assertEquals('fooVal', $model['foo']);
        $this->assertEquals('barVal', $model['bar']);
        $this->assertEquals('theLocation', $model['location']);
    }

    /**
     * @test
     */
    public function shouldThrowReplyWhenStatusCheckoutIncomplete()
    {
        $snippet = 'theSnippet';
        $expectedContent = 'theTemplateContent';
        $expectedTemplateName = 'theTemplateName';
        $expectedContext = array('snippet' => $snippet);

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->will($this->returnCallback(function (RenderTemplate $request) use ($testCase, $expectedTemplateName, $expectedContext, $expectedContent) {
                $testCase->assertEquals($expectedTemplateName, $request->getTemplateName());
                $testCase->assertEquals($expectedContext, $request->getParameters());

                $request->setResult($expectedContent);
            }))
        ;

        $action = new AuthorizeAction($expectedTemplateName);
        $action->setGateway($gatewayMock);

        try {
            $action->execute(new Authorize(array(
                'location' => 'aLocation',
                'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
                'gui' => array('snippet' => $snippet),
                'merchant' => [
                    'confirmation_uri' => 'theConfirmationUri',
                    'push_uri' => 'thePushUri',
                    'checkout_uri' => 'theCheckoutUri',
                    'terms_uri' => 'theTermsUri',
                ]
            )));
        } catch (HttpResponse $reply) {
            $this->assertEquals($expectedContent, $reply->getContent());

            return;
        }

        $this->fail('Exception expected to be throw');
    }

    /**
     * @test
     */
    public function shouldNotThrowReplyWhenStatusNotSet()
    {
        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($this->createGatewayMock());

        $action->execute(new Authorize(array(
            'location' => 'aLocation',
            'gui' => array('snippet' => 'theSnippet'),
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'push_uri' => 'thePushUri',
                'checkout_uri' => 'theCheckoutUri',
                'terms_uri' => 'theTermsUri',
            ]
        )));
    }

    /**
     * @test
     */
    public function shouldNotThrowReplyWhenStatusCreated()
    {
        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($this->createGatewayMock());

        $action->execute(new Authorize(array(
            'location' => 'aLocation',
            'status' => Constants::STATUS_CREATED,
            'gui' => array('snippet' => 'theSnippet'),
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'push_uri' => 'thePushUri',
                'checkout_uri' => 'theCheckoutUri',
                'terms_uri' => 'theTermsUri',
            ]
        )));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The push_uri fields are required.
     */
    public function shouldThrowIfPushUriNotSet()
    {
        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($this->createGatewayMock());

        $action->execute(new Authorize([
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'checkout_uri' => 'theCheckoutUri',
                'terms_uri' => 'theTermsUri',
            ]
        ]));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The confirmation_uri fields are required.
     */
    public function shouldThrowIfConfirmUriNotSet()
    {
        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($this->createGatewayMock());

        $action->execute(new Authorize([
            'merchant' => [
                'checkout_uri' => 'theCheckoutUri',
                'terms_uri' => 'theTermsUri',
                'push_uri' => 'thePushUri',
            ]
        ]));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The checkout_uri fields are required.
     */
    public function shouldThrowIfCheckoutUriNotSetNeitherInConfigNorPayment()
    {
        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($this->createGatewayMock());
        $action->setApi(new Config());

        $action->execute(new Authorize([
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'terms_uri' => 'theTermsUri',
                'push_uri' => 'thePushUri',
            ]
        ]));
    }

    /**
     * @test
     */
    public function shouldUseCheckoutUriFromConfig()
    {
        $config = new Config();
        $config->checkoutUri = 'theCheckoutUrl';

        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($this->createGatewayMock());
        $action->setApi($config);

        $action->execute(new Authorize([
            'location' => 'aLocation',
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'terms_uri' => 'theTermsUri',
                'push_uri' => 'thePushUri',
            ]
        ]));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The terms_uri fields are required.
     */
    public function shouldThrowIfTermsUriNotSetNeitherInConfigNorPayment()
    {
        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($this->createGatewayMock());
        $action->setApi(new Config());

        $action->execute(new Authorize([
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'push_uri' => 'thePushUri',
                'checkout_uri' => 'theCheckoutUri',
            ]
        ]));
    }

    /**
     * @test
     */
    public function shouldUseTermsUriFromConfig()
    {
        $config = new Config();
        $config->termsUri = 'theTermsUrl';

        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($this->createGatewayMock());
        $action->setApi($config);

        $action->execute(new Authorize([
            'location' => 'aLocation',
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'checkout_uri' => 'theCheckoutUri',
                'push_uri' => 'thePushUri',
            ]
        ]));
    }

    /**
     * @test
     */
    public function shouldUseTargetUrlFromRequestTokenAsConfirmationIfNotSet()
    {
        $config = new Config();

        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($this->createGatewayMock());
        $action->setApi($config);

        $token = new Token();
        $token->setTargetUrl('theTargetUrl');

        $authorize = new Authorize($token);
        $authorize->setModel([
            'location' => 'aLocation',
            'merchant' => [
                'terms_uri' => 'theTermsUri',
                'checkout_uri' => 'theCheckoutUri',
                'push_uri' => 'thePushUri',
            ]
        ]);

        $action->execute($authorize);
    }

    /**
     * @test
     */
    public function shouldGeneratePushUriIfNotSet()
    {
        $config = new Config();
        $config->termsUri = 'theTermsUri';

        $token = new Token();
        $token->setTargetUrl('theTargetUrl');
        $token->setGatewayName('theGatewayName');
        $token->setDetails($identity = new Identity('id', 'class'));

        $notifyToken = new Token();
        $notifyToken->setTargetUrl('theNotifyUrl');

        $tokenFactory = $this->getMock(GenericTokenFactoryInterface::class);
        $tokenFactory
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with('theGatewayName', $this->identicalTo($identity))
            ->will($this->returnValue($notifyToken))
        ;

        $action = new AuthorizeAction('aTemplate');
        $action->setGateway($this->createGatewayMock());
        $action->setApi($config);
        $action->setGenericTokenFactory($tokenFactory);



        $authorize = new Authorize($token);
        $authorize->setModel([
            'location' => 'aLocation',
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'checkout_uri' => 'theCheckoutUri',
                'terms_uri' => 'theTermsUri',
            ]
        ]);

        $action->execute($authorize);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->getMock('Klarna_Checkout_Order', array(), array(), '', false);
    }
}
