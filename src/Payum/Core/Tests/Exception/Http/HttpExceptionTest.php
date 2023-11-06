<?php
namespace Payum\Core\Tests\Exception\Http;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Payum\Core\Exception\Http\HttpException;
use PHPUnit\Framework\TestCase;

class HttpExceptionTest extends TestCase
{
    public function testShouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass(\Payum\Core\Exception\Http\HttpException::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Exception\RuntimeException::class));
    }

    public function testShouldImplementHttpExceptionInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Exception\Http\HttpException::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Exception\Http\HttpExceptionInterface::class));
    }

    public function testShouldAllowSetRequest()
    {
        $exception = new HttpException();

        $request = $this->createMock(\Psr\Http\Message\RequestInterface::class);
        $exception->setRequest($request);
        $this->assertSame($request, $exception->getRequest());
    }

    public function testShouldAllowGetPreviouslySetRequest()
    {
        $exception = new HttpException();

        $exception->setRequest($expectedRequest = $this->createMock(\Psr\Http\Message\RequestInterface::class));

        $this->assertSame($expectedRequest, $exception->getRequest());
    }

    public function testShouldAllowSetResponse()
    {
        $exception = new HttpException();

        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $exception->setResponse($response);
        $this->assertSame($response, $exception->getResponse());
    }

    public function testShouldAllowGetPreviouslySetResponse()
    {
        $exception = new HttpException();

        $exception->setResponse($expectedResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class));

        $this->assertSame($expectedResponse, $exception->getResponse());
    }

    public function testShouldAllowCreateHttpExceptionFromRequestAndResponse()
    {
        $request = new Request('GET', 'http://example.com/foobar');

        $response = new Response(404);

        $httpException = HttpException::factory($request, $response);

        $this->assertInstanceOf(\Payum\Core\Exception\Http\HttpException::class, $httpException);
        $this->assertSame($request, $httpException->getRequest());
        $this->assertSame($response, $httpException->getResponse());

        $this->assertSame(
            "Client error response\n[status code] 404\n[reason phrase] Not Found\n[url] http://example.com/foobar",
            $httpException->getMessage()
        );
        $this->assertSame(404, $httpException->getCode());
    }
}
