<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Generic;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Klarna\Checkout\Action\AuthorizeAction;
use Payum\Klarna\Checkout\Config;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizeActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldImplementsGenericTokenFactoryAwareInterface()
    {
        $rc = new \ReflectionClass(AuthorizeAction::class);
        $rc = new \ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(GenericTokenFactoryAwareInterface::class));
    }

    public function testShouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportAuthorizeWithArrayAsModel()
    {
        $action = new AuthorizeAction('aTemplate');

        $this->assertTrue($action->supports(new Authorize(array())));
    }

    public function testShouldNotSupportAnythingNotAuthorize()
    {
        $action = new AuthorizeAction('aTemplate');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportAuthorizeWithNotArrayAccessModel()
    {
        $action = new AuthorizeAction('aTemplate');

        $this->assertFalse($action->supports(new Authorize(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new AuthorizeAction('aTemplate');

        $action->execute(new \stdClass());
    }

    public function testShouldSubExecuteSyncIfModelHasLocationSet()
    {
        $this->expectException(\Payum\Core\Reply\HttpResponse::class);
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

    public function testShouldSubExecuteCreateOrderRequestIfStatusAndLocationNotSet()
    {
        $orderMock = $this->createOrderMock();
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->willReturn(array(
                'foo' => 'fooVal',
                'bar' => 'barVal',
            ))
        ;
        $orderMock
            ->expects($this->once())
            ->method('getLocation')
            ->willReturn('theLocation')
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(CreateOrder::class))
            ->willReturnCallback(function (CreateOrder $request) use ($orderMock) {
                $request->setOrder($orderMock);
            })
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

        $this->assertSame('fooVal', $model['foo']);
        $this->assertSame('barVal', $model['bar']);
        $this->assertSame('theLocation', $model['location']);
    }

    public function testShouldThrowReplyWhenStatusCheckoutIncomplete()
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
            ->willReturnCallback(function (RenderTemplate $request) use ($testCase, $expectedTemplateName, $expectedContext, $expectedContent) {
                $testCase->assertSame($expectedTemplateName, $request->getTemplateName());
                $testCase->assertSame($expectedContext, $request->getParameters());

                $request->setResult($expectedContent);
            })
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
            $this->assertSame($expectedContent, $reply->getContent());

            return;
        }

        $this->fail('Exception expected to be throw');
    }

    public function testShouldNotThrowReplyWhenStatusNotSet()
    {
        $action = new AuthorizeAction('aTemplate');
        $gateway = $this->createGatewayMock();
        $action->setGateway($gateway);

        $gateway->expects($this->once())
            ->method('execute')
            ->with(new Sync(ArrayObject::ensureArrayObject([
                'location' => 'aLocation',
                'gui' => array('snippet' => 'theSnippet'),
                'merchant' => [
                    'confirmation_uri' => 'theConfirmationUri',
                    'push_uri' => 'thePushUri',
                    'checkout_uri' => 'theCheckoutUri',
                    'terms_uri' => 'theTermsUri',
                ]
            ]))
        );

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

    public function testShouldNotThrowReplyWhenStatusCreated()
    {
        $action = new AuthorizeAction('aTemplate');
        $gateway = $this->createGatewayMock();
        $action->setGateway($gateway);

        $gateway->expects($this->once())
            ->method('execute')
            ->with(new Sync(ArrayObject::ensureArrayObject([
                'location' => 'aLocation',
                'status' => Constants::STATUS_CREATED,
                'gui' => array('snippet' => 'theSnippet'),
                'merchant' => [
                    'confirmation_uri' => 'theConfirmationUri',
                    'push_uri' => 'thePushUri',
                    'checkout_uri' => 'theCheckoutUri',
                    'terms_uri' => 'theTermsUri',
                ]]
            ))
        );

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

    public function testShouldThrowIfPushUriNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The push_uri fields are required.');
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

    public function testShouldThrowIfConfirmUriNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The confirmation_uri fields are required.');
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

    public function testShouldThrowIfCheckoutUriNotSetNeitherInConfigNorPayment()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The checkout_uri fields are required.');
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

    public function testShouldUseCheckoutUriFromConfig()
    {
        $config = new Config();
        $config->checkoutUri = 'theCheckoutUrl';

        $action = new AuthorizeAction('aTemplate');
        $gateway = $this->createGatewayMock();
        $gateway->expects($this->once())
            ->method('execute')
            ->with(new Sync(ArrayObject::ensureArrayObject(['location' => 'aLocation', 'merchant' => ['checkout_uri' => 'theCheckoutUrl', 'confirmation_uri' => 'theConfirmationUri', 'terms_uri' => 'theTermsUri', 'push_uri' => 'thePushUri']])));

        $action->setGateway($gateway);
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

    public function testShouldThrowIfTermsUriNotSetNeitherInConfigNorPayment()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The terms_uri fields are required.');
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

    public function testShouldUseTermsUriFromConfig()
    {
        $config = new Config();
        $config->termsUri = 'theTermsUrl';

        $action = new AuthorizeAction('aTemplate');
        $gateway = $this->createGatewayMock();
        $action->setGateway($gateway);
        $action->setApi($config);

        $gateway->expects($this->once())
            ->method('execute')
            ->with(new Sync(ArrayObject::ensureArrayObject([
                'location' => 'aLocation',
                'merchant' => [
                    'confirmation_uri' => 'theConfirmationUri',
                    'checkout_uri' => 'theCheckoutUri',
                    'push_uri' => 'thePushUri',
                    'terms_uri' => 'theTermsUrl',
                ]
            ]))
        );

        $action->execute(new Authorize([
            'location' => 'aLocation',
            'merchant' => [
                'confirmation_uri' => 'theConfirmationUri',
                'checkout_uri' => 'theCheckoutUri',
                'push_uri' => 'thePushUri',
            ]
        ]));
    }

    public function testShouldUseTargetUrlFromRequestTokenAsConfirmationIfNotSet()
    {
        $config = new Config();

        $action = new AuthorizeAction('aTemplate');
        $gateway = $this->createGatewayMock();
        $action->setGateway($gateway);
        $action->setApi($config);

        $token = new Token();
        $token->setTargetUrl('theTargetUrl');

        $gateway->expects($this->once())
            ->method('execute')
            ->with(new Sync(ArrayObject::ensureArrayObject([
                    'location' => 'aLocation',
                    'merchant' => [
                        'confirmation_uri' => 'theTargetUrl',
                        'checkout_uri' => 'theCheckoutUri',
                        'push_uri' => 'thePushUri',
                        'terms_uri' => 'theTermsUri',
                    ]
                ]))
            );

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

    public function testShouldGeneratePushUriIfNotSet()
    {
        $config = new Config();
        $config->termsUri = 'theTermsUri';

        $token = new Token();
        $token->setTargetUrl('theTargetUrl');
        $token->setGatewayName('theGatewayName');
        $token->setDetails($identity = new Identity('id', 'class'));

        $notifyToken = new Token();
        $notifyToken->setTargetUrl('theNotifyUrl');

        $tokenFactory = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenFactory
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with('theGatewayName', $this->identicalTo($identity))
            ->willReturn($notifyToken)
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
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }

    /**
     * @return MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->createMock('Klarna_Checkout_Order');
    }
}
