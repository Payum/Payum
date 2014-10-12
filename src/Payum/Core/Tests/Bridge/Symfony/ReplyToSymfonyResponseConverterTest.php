<?php
namespace Payum\Bundle\PayumBundle\Tests\EventListener;

use Payum\Core\Bridge\Symfony\Reply\HttpResponse as SymfonyHttpResponse;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class ReplyToSymfonyResponseConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new ReplyToSymfonyResponseConverter;
    }

    /**
     * @test
     */
    public function shouldReturnRedirectResponseIfPayumHttpRedirectReply()
    {
        $expectedUrl = '/foo/bar';
        
        $reply = new HttpRedirect($expectedUrl);

        $converter = new ReplyToSymfonyResponseConverter;

        $response = $converter->convert($reply);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals($expectedUrl, $response->getTargetUrl());
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldReturnResponseIfPayumHttpResponseReply()
    {
        $reply = new HttpResponse('theContent');

        $converter = new ReplyToSymfonyResponseConverter;

        $response = $converter->convert($reply);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('theContent', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldReturnResponseIfPayumHttpPostRedirectReply()
    {
        $reply = new HttpPostRedirect('anUrl', array('foo' => 'foo'));

        $converter = new ReplyToSymfonyResponseConverter;

        $response = $converter->convert($reply);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($reply->getContent(), $response->getContent());
    }

    /**
     * @test
     */
    public function shouldReturnResponseIfSymfonyHttpResponseReply()
    {
        $expectedResponse = new Response('foobar');

        $reply = new SymfonyHttpResponse($expectedResponse);

        $converter = new ReplyToSymfonyResponseConverter;

        $actualResponse = $converter->convert($reply);

        $this->assertSame($expectedResponse, $actualResponse);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Cannot convert reply Mock_Base_
     */
    public function shouldChangeReplyToLogicExceptionIfNotSupported()
    {
        $notSupportedReply = $this->getMock('Payum\Core\Reply\Base');

        $listener = new ReplyToSymfonyResponseConverter;

        $listener->convert($notSupportedReply);
    }
}