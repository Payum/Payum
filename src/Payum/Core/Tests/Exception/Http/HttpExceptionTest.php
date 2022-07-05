<?php

namespace Payum\Core\Tests\Exception\Http;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Payum\Core\Exception\Http\HttpException;
use PHPUnit\Framework\TestCase;

class HttpExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\Http\HttpException');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Exception\RuntimeException'));
    }

    /**
     * @test
     */
    public function shouldImplementHttpExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\Http\HttpException');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Exception\Http\HttpExceptionInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowSetRequest()
    {
        $exception = new HttpException();

        $request = $this->createMock('Psr\Http\Message\RequestInterface');
        $exception->setRequest($request);
        $this->assertSame($request, $exception->getRequest());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetRequest()
    {
        $exception = new HttpException();

        $exception->setRequest($expectedRequest = $this->createMock('Psr\Http\Message\RequestInterface'));

        $this->assertSame($expectedRequest, $exception->getRequest());
    }

    /**
     * @test
     */
    public function shouldAllowSetResponse()
    {
        $exception = new HttpException();

        $response = $this->createMock('Psr\Http\Message\ResponseInterface');
        $exception->setResponse($response);
        $this->assertSame($response, $exception->getResponse());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetResponse()
    {
        $exception = new HttpException();

        $exception->setResponse($expectedResponse = $this->createMock('Psr\Http\Message\ResponseInterface'));

        $this->assertSame($expectedResponse, $exception->getResponse());
    }

    /**
     * @test
     */
    public function shouldAllowCreateHttpExceptionFromRequestAndResponse()
    {
        $request = new Request('GET', 'http://example.com/foobar');

        $response = new Response(404);

        $httpException = HttpException::factory($request, $response);

        $this->assertInstanceOf('Payum\Core\Exception\Http\HttpException', $httpException);
        $this->assertSame($request, $httpException->getRequest());
        $this->assertSame($response, $httpException->getResponse());

        $this->assertSame(
            "Client error response\n[status code] 404\n[reason phrase] Not Found\n[url] http://example.com/foobar",
            $httpException->getMessage()
        );
        $this->assertSame(404, $httpException->getCode());
    }
}
