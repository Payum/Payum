<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Action\Api\ObtainTokenAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\ObtainToken;

class ObtainTokenActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(ObtainTokenAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(ObtainTokenAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowNotSupportedApiIfNotKeysGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new ObtainTokenAction('aTemplateName');

        $action->setApi('not keys instance');
    }

    public function testShouldSupportObtainTokenRequestWithArrayAccessModel()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertTrue($action->supports(new ObtainToken(array())));
    }

    public function testShouldNotSupportObtainTokenRequestWithNotArrayAccessModel()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertFalse($action->supports(new ObtainToken(new \stdClass())));
    }

    public function testShouldNotSupportNotObtainTokenRequest()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowRequestNotSupportedIfNotSupportedGiven()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action ObtainTokenAction is not supported the request stdClass.');
        $action = new ObtainTokenAction('aTemplateName');

        $action->execute(new \stdClass());
    }

    public function testThrowIfModelAlreadyHaveTokenSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The token has already been set.');
        $action = new ObtainTokenAction('aTemplateName');

        $action->execute(new ObtainToken(array(
            'card' => 'aToken',
        )));
    }

    public function testShouldRenderExpectedTemplateIfHttpRequestNotPOST()
    {
        $model = new \ArrayObject();
        $templateName = 'theTemplateName';
        $publishableKey = 'thePubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->method = 'GET';
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->willReturnCallback(function (RenderTemplate $request) use ($templateName, $publishableKey, $model) {
                $this->assertSame($templateName, $request->getTemplateName());

                $context = $request->getParameters();
                $this->assertArrayHasKey('model', $context);
                $this->assertArrayHasKey('publishable_key', $context);
                $this->assertSame($publishableKey, $context['publishable_key']);

                $request->setResult('theContent');
            })
        ;

        $action = new ObtainTokenAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new ObtainToken($model));
        } catch (HttpResponse $reply) {
            $this->assertSame('theContent', $reply->getContent());

            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    public function testShouldRenderTemplateIfHttpRequestPOSTButNotContainStripeToken()
    {
        $model = array();
        $templateName = 'aTemplateName';
        $publishableKey = 'aPubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
        ;

        $action = new ObtainTokenAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new ObtainToken($model));
        } catch (HttpResponse $reply) {
            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    public function testShouldSetTokenFromHttpRequestToObtainTokenRequestOnPOST()
    {
        $model = array();
        $templateName = 'aTemplateName';
        $publishableKey = 'aPubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
                $request->request = array('stripeToken' => 'theToken');
            })
        ;

        $action = new ObtainTokenAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        $action->execute($obtainToken = new ObtainToken($model));

        $model = $obtainToken->getModel();
        $this->assertSame('theToken', $model['card']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
