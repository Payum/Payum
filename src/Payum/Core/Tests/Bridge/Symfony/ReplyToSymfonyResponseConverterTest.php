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
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new ReplyToSymfonyResponseConverter();
    }

    /**
     * @test
     */
    public function shouldReturnRedirectResponseIfPayumHttpRedirectReply()
    {
        $expectedUrl = '/foo/bar';

        $reply = new HttpRedirect($expectedUrl);

        $converter = new ReplyToSymfonyResponseConverter();

        $response = $converter->convert($reply);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertStringContainsString('Redirecting to /foo/bar', $response->getContent());
        $this->assertEquals(302, $response->getStatusCode());

        $headers = $response->headers->all();
        $this->assertArrayHasKey('location', $headers);
        $this->assertNotEmpty($headers['location']);
        $this->assertEquals($expectedUrl, $headers['location'][0]);
    }

    /**
     * @test
     */
    public function shouldReturnResponseIfPayumHttpResponseReply()
    {
        $reply = new HttpResponse('theContent');

        $converter = new ReplyToSymfonyResponseConverter();

        $response = $converter->convert($reply);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('theContent', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldReturnResponseIfPayumHttpResponseReplyWithCustomStatusCodeAndHeaders()
    {
        $reply = new HttpResponse('theContent', 418, array(
            'foo' => 'fooVal',
            'bar' => 'bar',
        ));

        $converter = new ReplyToSymfonyResponseConverter();

        $response = $converter->convert($reply);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('theContent', $response->getContent());
        $this->assertEquals(418, $response->getStatusCode());
        $this->assertArrayHasKey('foo', $response->headers->all());
        $this->assertArrayHasKey('bar', $response->headers->all());
    }

    /**
     * @test
     */
    public function shouldReturnResponseIfPayumHttpPostRedirectReply()
    {
        $reply = new HttpPostRedirect('anUrl', array('foo' => 'foo'));

        $converter = new ReplyToSymfonyResponseConverter();

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

        $converter = new ReplyToSymfonyResponseConverter();

        $actualResponse = $converter->convert($reply);

        $this->assertSame($expectedResponse, $actualResponse);
    }

    /**
     * @test
     */
    public function shouldChangeReplyToLogicExceptionIfNotSupported()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot convert reply Mock_Base_');
        $notSupportedReply = $this->createMock('Payum\Core\Reply\Base');

        $listener = new ReplyToSymfonyResponseConverter();

        $listener->convert($notSupportedReply);
    }
}
