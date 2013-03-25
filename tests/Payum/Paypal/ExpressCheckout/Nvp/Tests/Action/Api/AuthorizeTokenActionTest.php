<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeTokenRequest;

class AuthorizeTokenActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseActionApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseActionApiAware'));
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
        
        $this->assertTrue($action->supports(new AuthorizeTokenRequest($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldSupportAuthorizeTokenRequestWithPaymentDetailsAsModel()
    {
        $action = new AuthorizeTokenAction();

        $this->assertTrue($action->supports(new AuthorizeTokenRequest(new PaymentDetails)));
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
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new AuthorizeTokenAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The TOKEN must be set. Have you executed SetExpressCheckoutAction?
     */
    public function throwIfModelNotHaveTokenSet()
    {
        $action = new AuthorizeTokenAction($this->createApiMock());

        $action->execute(new AuthorizeTokenRequest(new \ArrayObject()));
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
        
        $request = new AuthorizeTokenRequest(array(
            'TOKEN' => $expectedToken
        ));

        try {
            $action->execute($request);
        } catch (RedirectUrlInteractiveRequest $redirectUrlRequest) {
            $this->assertEquals($expectedRedirectUrl, $redirectUrlRequest->getUrl());
            
            return;
        }
        
        $this->fail('RedirectUrlInteractiveRequest exception was expected.');
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

        $request = new AuthorizeTokenRequest(array(
            'TOKEN' => 'aToken',
            //payer id means that the user already authorize the token. 
            //Entered his login\passowrd and press enter at paypal side.
            'PAYERID' => 'aPayerId'
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
        ;

        $action = new AuthorizeTokenAction();
        $action->setApi($apiMock);

        $request = new AuthorizeTokenRequest(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId'
        ), $force = true);

        try {
            $action->execute($request);
        } catch (RedirectUrlInteractiveRequest $redirectUrlRequest) {
            return;
        }

        $this->fail('RedirectUrlInteractiveRequest exception was expected.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}