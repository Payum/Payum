<?php
namespace Payum\Bundle\PayumBundle\Tests\EventListener;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Payum\Bundle\PayumBundle\EventListener\InteractiveRequestListener;
use Payum\Bundle\PayumBundle\Request\ResponseInteractiveRequest;
use Payum\Request\RedirectUrlInteractiveRequest;

class InteractiveRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new InteractiveRequestListener;
    }

    /**
     * @test
     */
    public function shouldDoNothingIfExceptionNotInstanceOfInteractiveRequest()
    {
        $expectedException = new Exception;
        
        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $expectedException
        );
        
        $listener = new InteractiveRequestListener;

        $listener->onKernelException($event);
        
        $this->assertNull($event->getResponse());
        $this->assertSame($expectedException, $event->getException());
        $this->assertFalse($event->isPropagationStopped());
    }

    /**
     * @test
     */
    public function shouldSetRedirectResponseIfExceptionInstanceOfRedirectUrlInteractiveRequest()
    {
        $expectedUrl = '/foo/bar';
        
        $interactiveRequest = new RedirectUrlInteractiveRequest($expectedUrl);

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $interactiveRequest
        );

        $listener = new InteractiveRequestListener;

        $listener->onKernelException($event);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $event->getResponse());
        $this->assertEquals($expectedUrl, $event->getResponse()->getTargetUrl());
        $this->assertSame($interactiveRequest, $event->getException());
    }

    /**
     * @test
     */
    public function shouldSetResponseIfExceptionInstanceOfResponseInteractiveRequest()
    {
        $expectedResponse = new Response('foobar');

        $interactiveRequest = new ResponseInteractiveRequest($expectedResponse);

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $interactiveRequest
        );

        $listener = new InteractiveRequestListener;

        $listener->onKernelException($event);

        $this->assertSame($expectedResponse, $event->getResponse());
        $this->assertSame($interactiveRequest, $event->getException());
    }

    /**
     * @test
     */
    public function shouldChangeInteractiveRequestToLogicExceptionIfNotSupported()
    {
        $expectedResponse = new Response('foobar');

        $interactiveRequest = $this->getMock('Payum\Request\BaseInteractiveRequest');

        $event = new GetResponseForExceptionEvent(
            $this->createHttpKernelMock(),
            new Request,
            'requestType',
            $interactiveRequest
        );

        $listener = new InteractiveRequestListener;

        $listener->onKernelException($event);

        $this->assertNull($event->getResponse());
        $this->assertInstanceOf('Payum\Exception\LogicException', $event->getException());
        $this->assertStringStartsWith(
            'Cannot convert interactive request Mock_BaseInteractiveRequest', 
            $event->getException()->getMessage()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpKernelInterface
     */
    protected function createHttpKernelMock()
    {
        return $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
    }
}