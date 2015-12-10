<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Reply\HttpRedirect;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken;

class AuthorizeTokenActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArgument()
    {
        new AuthorizeTokenAction();
    }

    /**
     * @test
     */
    public function shouldSupportAuthorizeTokenRequestWithArrayAccessAsModel()
    {
        $action = new AuthorizeTokenAction();

        $this->assertTrue($action->supports(new AuthorizeToken($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorizeTokenRequest()
    {
        $action = new AuthorizeTokenAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new AuthorizeTokenAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The TOKEN must be set by SetExpressCheckout request but it was not executed or failed. Review payment details model for more information
     */
    public function throwIfModelNotHaveTokenSet()
    {
        $action = new AuthorizeTokenAction($this->createApiMock());

        $action->execute(new AuthorizeToken(new \ArrayObject()));
    }

    /**
     * @test
     */
    public function throwRedirectUrlRequestIfModelNotHavePayerIdSet()
    {
        $expectedToken = 'theAuthToken';
        $expectedRedirectUrl = 'theRedirectUrl';

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getAuthorizeTokenUrl')
            ->with($expectedToken)
            ->will($this->returnValue($expectedRedirectUrl))
        ;

        $action = new AuthorizeTokenAction();
        $action->setApi($apiMock);

        $model = new \ArrayObject();
        $model['TOKEN'] = $expectedRedirectUrl;

        $request = new AuthorizeToken(array(
            'TOKEN' => $expectedToken,
        ));

        try {
            $action->execute($request);
        } catch (HttpRedirect $reply) {
            $this->assertEquals($expectedRedirectUrl, $reply->getUrl());

            return;
        }

        $this->fail('HttpRedirect reply was expected to be thrown.');
    }

    /**
     * @test
     */
    public function shouldPassAuthorizeTokenCustomParametersToApi()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getAuthorizeTokenUrl')
            ->with('aToken', array(
                'useraction' => 'theUserAction',
                'cmd' => 'theCmd',
            ))
            ->will($this->returnValue('theRedirectUrl'))
        ;

        $action = new AuthorizeTokenAction();
        $action->setApi($apiMock);

        $request = new AuthorizeToken(array(
            'TOKEN' => 'aToken',
            'AUTHORIZE_TOKEN_USERACTION' => 'theUserAction',
            'AUTHORIZE_TOKEN_CMD' => 'theCmd',
        ));

        try {
            $action->execute($request);
        } catch (HttpRedirect $reply) {
            $this->assertEquals('theRedirectUrl', $reply->getUrl());

            return;
        }

        $this->fail('HttpRedirect reply was expected to be thrown.');
    }

    /**
     * @test
     */
    public function shouldDoNothingIfUserAlreadyAuthorizedToken()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('getAuthorizeTokenUrl')
        ;

        $action = new AuthorizeTokenAction();
        $action->setApi($apiMock);

        $request = new AuthorizeToken(array(
            'TOKEN' => 'aToken',
            //payer id means that the user already authorize the token.
            //Entered his login\passowrd and press enter at paypal side.
            'PAYERID' => 'aPayerId',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function throwRedirectUrlRequestIfForceTrue()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getAuthorizeTokenUrl')
            ->will($this->returnValue('theRedirectUrl'))
        ;

        $action = new AuthorizeTokenAction();
        $action->setApi($apiMock);

        $request = new AuthorizeToken(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
        ), $force = true);

        try {
            $action->execute($request);
        } catch (HttpRedirect $reply) {
            $this->assertEquals('theRedirectUrl', $reply->getUrl());

            return;
        }

        $this->fail('HttpRedirect reply was expected to be thrown.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
