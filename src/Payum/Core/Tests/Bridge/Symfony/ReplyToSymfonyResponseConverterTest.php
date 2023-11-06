<?php
namespace Payum\Core\Tests\Bridge\Symfony;

use Payum\Core\Bridge\Symfony\Reply\HttpResponse as SymfonyHttpResponse;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ReplyToSymfonyResponseConverterTest extends TestCase
{
    public function testShouldReturnRedirectResponseIfPayumHttpRedirectReply()
    {
        $expectedUrl = '/foo/bar';

        $reply = new HttpRedirect($expectedUrl);

        $converter = new ReplyToSymfonyResponseConverter();

        $response = $converter->convert($reply);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertStringContainsString('Redirecting to /foo/bar', $response->getContent());
        $this->assertSame(302, $response->getStatusCode());

        $headers = $response->headers->all();
        $this->assertArrayHasKey('location', $headers);
        $this->assertNotEmpty($headers['location']);
        $this->assertSame($expectedUrl, $headers['location'][0]);
    }

    public function testShouldReturnResponseIfPayumHttpResponseReply()
    {
        $reply = new HttpResponse('theContent');

        $converter = new ReplyToSymfonyResponseConverter();

        $response = $converter->convert($reply);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('theContent', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testShouldReturnResponseIfPayumHttpResponseReplyWithCustomStatusCodeAndHeaders()
    {
        $reply = new HttpResponse('theContent', 418, array(
            'foo' => 'fooVal',
            'bar' => 'bar',
        ));

        $converter = new ReplyToSymfonyResponseConverter();

        $response = $converter->convert($reply);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('theContent', $response->getContent());
        $this->assertSame(418, $response->getStatusCode());
        $this->assertArrayHasKey('foo', $response->headers->all());
        $this->assertArrayHasKey('bar', $response->headers->all());
    }

    public function testShouldReturnResponseIfPayumHttpPostRedirectReply()
    {
        $reply = new HttpPostRedirect('anUrl', array('foo' => 'foo'));

        $converter = new ReplyToSymfonyResponseConverter();

        $response = $converter->convert($reply);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($reply->getContent(), $response->getContent());
    }

    public function testShouldReturnResponseIfSymfonyHttpResponseReply()
    {
        $expectedResponse = new Response('foobar');

        $reply = new SymfonyHttpResponse($expectedResponse);

        $converter = new ReplyToSymfonyResponseConverter();

        $actualResponse = $converter->convert($reply);

        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testShouldChangeReplyToLogicExceptionIfNotSupported()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot convert reply Mock_Base_');
        $notSupportedReply = $this->createMock('Payum\Core\Reply\Base');

        $listener = new ReplyToSymfonyResponseConverter();

        $listener->convert($notSupportedReply);
    }
}
