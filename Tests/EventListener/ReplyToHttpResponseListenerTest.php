<?php
namespace Payum\Bundle\PayumBundle\Tests\EventListener;

use Payum\Bundle\PayumBundle\EventListener\ReplyToHttpResponseListener;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse as SymfonyHttpResponse;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ReplyToHttpResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new ReplyToHttpResponseListener;
    }

    /**
     * @test
     */
    public function shouldDoNothingIfExceptionNotInstanceOfReply()
    {
        $expectedException = new Exception;
        
        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $expectedException
        );
        
        $listener = new ReplyToHttpResponseListener;

        $listener->onKernelException($event);
        
        $this->assertNull($event->getResponse());
        $this->assertSame($expectedException, $event->getException());
        $this->assertFalse($event->isPropagationStopped());
    }

    /**
     * @test
     */
    public function shouldSetRedirectResponseIfExceptionHttpRedirectReply()
    {
        $expectedUrl = '/foo/bar';
        
        $reply = new HttpRedirect($expectedUrl);

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $reply
        );

        $listener = new ReplyToHttpResponseListener;

        $listener->onKernelException($event);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $event->getResponse());
        $this->assertEquals($expectedUrl, $event->getResponse()->getTargetUrl());
        $this->assertSame($reply, $event->getException());
    }

    /**
     * @test
     */
    public function shouldSetXStatusCodeWhenExceptionInstanceOfHttpRedirectReply()
    {
        $reply = new HttpRedirect('/foo/bar');

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $reply
        );

        $listener = new ReplyToHttpResponseListener;

        $listener->onKernelException($event);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $event->getResponse());
        $this->assertTrue($event->getResponse()->headers->has('X-Status-Code'));
        $this->assertEquals(302, $event->getResponse()->headers->get('X-Status-Code'));
    }

    /**
     * @test
     */
    public function shouldSetResponseIfExceptionInstanceOfSymfonyHttpResponseReply()
    {
        $expectedResponse = new Response('foobar');

        $reply = new SymfonyHttpResponse($expectedResponse);

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $reply
        );

        $listener = new ReplyToHttpResponseListener;

        $listener->onKernelException($event);

        $this->assertSame($expectedResponse, $event->getResponse());
        $this->assertSame($reply, $event->getException());
    }

    /**
     * @test
     */
    public function shouldSetXStatusCodeWhenExceptionInstanceOfSymfonyHttpResponseReply()
    {
        $expectedStatus = 555;

        $response = new Response('foobar', $expectedStatus);

        $reply = new SymfonyHttpResponse($response);

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $reply
        );

        $listener = new ReplyToHttpResponseListener;

        $listener->onKernelException($event);

        //guard
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $event->getResponse());

        $this->assertTrue($event->getResponse()->headers->has('X-Status-Code'));
        $this->assertEquals(555, $event->getResponse()->headers->get('X-Status-Code'));
    }

    /**
     * @test
     */
    public function shouldNotSetXStatusCodeIfAlreadySetWhenExceptionInstanceOfSymfonyHttpResponseReply()
    {
        $expectedStatus = 555;

        $response = new Response('foobar', $expectedStatus);
        $response->headers->set('X-Status-Code', 666);

        $reply = new SymfonyHttpResponse($response);

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $reply
        );

        $listener = new ReplyToHttpResponseListener;

        $listener->onKernelException($event);

        //guard
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $event->getResponse());

        $this->assertTrue($event->getResponse()->headers->has('X-Status-Code'));
        $this->assertEquals(666, $event->getResponse()->headers->get('X-Status-Code'));
    }

    /**
     * @test
     */
    public function shouldSetResponseAndStatus200IfExceptionInstanceOfHttpResponseReply()
    {
        $reply = new HttpResponse('theContent');

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $reply
        );

        $listener = new ReplyToHttpResponseListener;

        $listener->onKernelException($event);

        $this->assertSame('theContent', $event->getResponse()->getContent());
        $this->assertEquals(200, $event->getResponse()->headers->get('X-Status-Code'));
    }

    /**
     * @test
     */
    public function shouldChangeReplyToLogicExceptionIfNotSupported()
    {
        $notSupportedReply = $this->getMock('Payum\Core\Reply\Base');

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $notSupportedReply
        );

        $listener = new ReplyToHttpResponseListener;

        $listener->onKernelException($event);

        $this->assertNull($event->getResponse());
        $this->assertInstanceOf('Payum\Core\Exception\LogicException', $event->getException());
        $this->assertStringStartsWith(
            'Cannot convert reply Mock_Base_',
            $event->getException()->getMessage()
        );
    }

    /**
     * @test
     */
    public function shouldSetResponseIfExceptionInstanceOfHttpPostRedirectReply()
    {
        $reply = new HttpPostRedirect('anUrl', array('foo' => 'foo'));

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $reply
        );

        $listener = new ReplyToHttpResponseListener;

        $listener->onKernelException($event);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $event->getResponse());
        $this->assertEquals(200, $event->getResponse()->getStatusCode());
        $this->assertEquals($reply->getContent(), $event->getResponse()->getContent());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpKernelInterface
     */
    protected function createHttpKernelMock()
    {
        return $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    }
}