<?php
namespace Payum\Bundle\PayumBundle\Tests\Controller;

use Payum\Bundle\PayumBundle\Controller\CaptureController;
use Payum\Core\Model\Token;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CaptureControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfController()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\Controller\CaptureController');

        $this->assertTrue($rc->isSubclassOf('Symfony\Bundle\FrameworkBundle\Controller\Controller'));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage This controller requires session to be started.
     */
    public function throwBadRequestIfSessionNotStartedOnDoSessionAction()
    {
        $controller = new CaptureController;

        $request = Request::create('/');

        //guard
        $this->assertNull($request->getSession());

        $controller->doSessionTokenAction($request);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage This controller requires token hash to be stored in the session.
     */
    public function throwBadRequestIfSessionNotContainPayumTokenOnDoSessionAction()
    {
        $controller = new CaptureController;

        $request = Request::create('/');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $controller->doSessionTokenAction($request);
    }

    /**
     * @test
     */
    public function shouldDoRedirectToCaptureWithTokenUrl()
    {
        $routerMock = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $routerMock
            ->expects($this->once())
            ->method('generate')
            ->with('payum_capture_do', array(
                'payum_token' => 'theToken',
                'foo' => 'fooVal',
            ))
            ->will($this->returnValue('/payment/capture/theToken?foo=fooVal'))
        ;

        $container = new Container;
        $container->set('router', $routerMock);

        $controller = new CaptureController;
        $controller->setContainer($container);

        $request = Request::create('/');
        $request->query->set('foo', 'fooVal');

        $request->setSession(new Session(new MockArraySessionStorage()));
        $request->getSession()->set('payum_token', 'theToken');

        $response = $controller->doSessionTokenAction($request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('/payment/capture/theToken?foo=fooVal', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function shouldExecuteCaptureRequest()
    {
        $request = Request::create('/');
        $request->query->set('foo', 'fooVal');

        $token = new Token;
        $token->setPaymentName('thePayment');
        $token->setAfterUrl('http://example.com/theAfterUrl');

        $tokenVerifierMock = $this->getMock('Payum\Core\Security\HttpRequestVerifierInterface');
        $tokenVerifierMock
            ->expects($this->once())
            ->method('verify')
            ->with($this->identicalTo($request))
            ->will($this->returnValue($token))
        ;
        $tokenVerifierMock
            ->expects($this->once())
            ->method('invalidate')
            ->with($this->identicalTo($token))
        ;

        $paymentMock = $this->getMock('Payum\Core\PaymentInterface');
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Capture'))
        ;

        $registryMock = $this->getMock('Payum\Core\Registry\RegistryInterface');
        $registryMock
            ->expects($this->once())
            ->method('getPayment')
            ->with('thePayment')
            ->will($this->returnValue($paymentMock))
        ;

        $container = new Container;
        $container->set('payum', $registryMock);
        $container->set('payum.security.http_request_verifier', $tokenVerifierMock);

        $controller = new CaptureController;
        $controller->setContainer($container);

        $response = $controller->doAction($request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('http://example.com/theAfterUrl', $response->getTargetUrl());
    }
}
