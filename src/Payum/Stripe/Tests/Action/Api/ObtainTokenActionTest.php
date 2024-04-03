<?php

namespace Payum\Stripe\Tests\Action\Api;

use ArrayObject;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Action\Api\ObtainTokenAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\ObtainToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class ObtainTokenActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(ObtainTokenAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldImplementsApiAwareInterface(): void
    {
        $rc = new ReflectionClass(ObtainTokenAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowNotSupportedApiIfNotKeysGivenAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $action = new ObtainTokenAction('aTemplateName');

        $action->setApi('not keys instance');
    }

    public function testShouldSupportObtainTokenRequestWithArrayAccessModel(): void
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertTrue($action->supports(new ObtainToken([])));
    }

    public function testShouldNotSupportObtainTokenRequestWithNotArrayAccessModel(): void
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertFalse($action->supports(new ObtainToken(new stdClass())));
    }

    public function testShouldNotSupportNotObtainTokenRequest(): void
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowRequestNotSupportedIfNotSupportedGiven(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action ObtainTokenAction is not supported the request stdClass.');
        $action = new ObtainTokenAction('aTemplateName');

        $action->execute(new stdClass());
    }

    public function testThrowIfModelAlreadyHaveTokenSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The token has already been set.');
        $action = new ObtainTokenAction('aTemplateName');

        $action->execute(new ObtainToken([
            'card' => 'aToken',
        ]));
    }

    public function testShouldRenderExpectedTemplateIfHttpRequestNotPOST(): void
    {
        $model = new ArrayObject();
        $templateName = 'theTemplateName';
        $publishableKey = 'thePubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(RenderTemplate::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request): void {
                    $request->method = 'GET';
                }),
                $this->returnCallback(function (RenderTemplate $request) use ($templateName, $publishableKey, $model): void {
                    $this->assertSame($templateName, $request->getTemplateName());

                    $context = $request->getParameters();
                    $this->assertArrayHasKey('model', $context);
                    $this->assertArrayHasKey('publishable_key', $context);
                    $this->assertSame($publishableKey, $context['publishable_key']);

                    $request->setResult('theContent');
                })
            )
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

    public function testShouldRenderTemplateIfHttpRequestPOSTButNotContainStripeToken(): void
    {
        $model = [];
        $templateName = 'aTemplateName';
        $publishableKey = 'aPubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(RenderTemplate::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request): void {
                    $request->method = 'POST';
                })
            )
        ;

        $action = new ObtainTokenAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new ObtainToken($model));
        } catch (HttpResponse) {
            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    public function testShouldSetTokenFromHttpRequestToObtainTokenRequestOnPOST(): void
    {
        $model = [];
        $templateName = 'aTemplateName';
        $publishableKey = 'aPubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request): void {
                $request->method = 'POST';
                $request->request = [
                    'stripeToken' => 'theToken',
                ];
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
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
