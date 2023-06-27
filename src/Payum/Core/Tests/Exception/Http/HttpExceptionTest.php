<?php

namespace Payum\Core\Tests\Exception\Http;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\Http\HttpExceptionInterface;
use Payum\Core\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

class HttpExceptionTest extends TestCase
{
    public function testShouldBeSubClassOfRuntimeException(): void
    {
        $rc = new ReflectionClass(HttpException::class);

        $this->assertTrue($rc->isSubclassOf(RuntimeException::class));
    }

    public function testShouldImplementHttpExceptionInterface(): void
    {
        $rc = new ReflectionClass(HttpException::class);

        $this->assertTrue($rc->isSubclassOf(HttpExceptionInterface::class));
    }

    public function testShouldAllowSetRequest(): void
    {
        $exception = new HttpException();

        $request = $this->createMock(RequestInterface::class);
        $exception->setRequest($request);
        $this->assertSame($request, $exception->getRequest());
    }

    public function testShouldAllowGetPreviouslySetRequest(): void
    {
        $exception = new HttpException();

        $exception->setRequest($expectedRequest = $this->createMock(RequestInterface::class));

        $this->assertSame($expectedRequest, $exception->getRequest());
    }

    public function testShouldAllowSetResponse(): void
    {
        $exception = new HttpException();

        $response = $this->createMock(ResponseInterface::class);
        $exception->setResponse($response);
        $this->assertSame($response, $exception->getResponse());
    }

    public function testShouldAllowGetPreviouslySetResponse(): void
    {
        $exception = new HttpException();

        $exception->setResponse($expectedResponse = $this->createMock(ResponseInterface::class));

        $this->assertSame($expectedResponse, $exception->getResponse());
    }

    public function testShouldAllowCreateHttpExceptionFromRequestAndResponse(): void
    {
        $request = new Request('GET', 'http://example.com/foobar');

        $response = new Response(404);

        $httpException = HttpException::factory($request, $response);

        $this->assertInstanceOf(HttpException::class, $httpException);
        $this->assertSame($request, $httpException->getRequest());
        $this->assertSame($response, $httpException->getResponse());

        $this->assertSame(
            "Client error response\n[status code] 404\n[reason phrase] Not Found\n[url] http://example.com/foobar",
            $httpException->getMessage()
        );
        $this->assertSame(404, $httpException->getCode());
    }
}
