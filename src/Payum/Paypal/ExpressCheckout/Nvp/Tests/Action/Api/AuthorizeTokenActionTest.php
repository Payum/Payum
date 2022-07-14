<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AuthorizeTokenActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(AuthorizeTokenAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface(): void
    {
        $rc = new ReflectionClass(AuthorizeTokenAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportAuthorizeTokenRequestWithArrayAccessAsModel(): void
    {
        $action = new AuthorizeTokenAction();

        $this->assertTrue($action->supports(new AuthorizeToken($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotAuthorizeTokenRequest(): void
    {
        $action = new AuthorizeTokenAction($this->createApiMock());

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new AuthorizeTokenAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    public function testThrowIfModelNotHaveTokenSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The TOKEN must be set by SetExpressCheckout request but it was not executed or failed. Review payment details model for more information');
        $action = new AuthorizeTokenAction($this->createApiMock());

        $action->execute(new AuthorizeToken(new ArrayObject()));
    }

    public function testThrowRedirectUrlRequestIfModelNotHavePayerIdSet(): void
    {
        $expectedToken = 'theAuthToken';
        $expectedRedirectUrl = 'theRedirectUrl';

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getAuthorizeTokenUrl')
            ->with($expectedToken)
            ->willReturn($expectedRedirectUrl)
        ;

        $action = new AuthorizeTokenAction();
        $action->setApi($apiMock);

        $model = new ArrayObject();
        $model['TOKEN'] = $expectedRedirectUrl;

        $request = new AuthorizeToken([
            'TOKEN' => $expectedToken,
        ]);

        try {
            $action->execute($request);
        } catch (HttpRedirect $reply) {
            $this->assertSame($expectedRedirectUrl, $reply->getUrl());

            return;
        }

        $this->fail('HttpRedirect reply was expected to be thrown.');
    }

    public function testShouldPassAuthorizeTokenCustomParametersToApi(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getAuthorizeTokenUrl')
            ->with('aToken', [
                'useraction' => 'theUserAction',
                'cmd' => 'theCmd',
            ])
            ->willReturn('theRedirectUrl')
        ;

        $action = new AuthorizeTokenAction();
        $action->setApi($apiMock);

        $request = new AuthorizeToken([
            'TOKEN' => 'aToken',
            'AUTHORIZE_TOKEN_USERACTION' => 'theUserAction',
            'AUTHORIZE_TOKEN_CMD' => 'theCmd',
        ]);

        try {
            $action->execute($request);
        } catch (HttpRedirect $reply) {
            $this->assertSame('theRedirectUrl', $reply->getUrl());

            return;
        }

        $this->fail('HttpRedirect reply was expected to be thrown.');
    }

    public function testShouldDoNothingIfUserAlreadyAuthorizedToken(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('getAuthorizeTokenUrl')
        ;

        $action = new AuthorizeTokenAction();
        $action->setApi($apiMock);

        $request = new AuthorizeToken([
            'TOKEN' => 'aToken',
            //payer id means that the user already authorize the token.
            //Entered his login\passowrd and press enter at paypal side.
            'PAYERID' => 'aPayerId',
        ]);

        $action->execute($request);
    }

    public function testThrowRedirectUrlRequestIfForceTrue(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getAuthorizeTokenUrl')
            ->willReturn('theRedirectUrl')
        ;

        $action = new AuthorizeTokenAction();
        $action->setApi($apiMock);

        $request = new AuthorizeToken([
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
        ], $force = true);

        try {
            $action->execute($request);
        } catch (HttpRedirect $reply) {
            $this->assertSame('theRedirectUrl', $reply->getUrl());

            return;
        }

        $this->fail('HttpRedirect reply was expected to be thrown.');
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, [], [], '', false);
    }
}
