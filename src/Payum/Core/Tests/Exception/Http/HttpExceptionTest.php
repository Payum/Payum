<?php
namespace Payum\Core\Tests\Exception\Http;

use Buzz\Message\Request;
use Buzz\Message\Response;
use Payum\Core\Exception\Http\HttpException;

class HttpExceptionTest extends \PHPUnit_Framework_TestCase
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
    public function couldBeConstructedSameAsStandardException()
    {
        new HttpException('aMessage', 404);
    }

    /**
     * @test
     */
    public function shouldAllowSetRequest()
    {
        $exception = new HttpException();

        $exception->setRequest(new Request());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetRequest()
    {
        $exception = new HttpException();

        $exception->setRequest($expectedRequest = new Request());

        $this->assertSame($expectedRequest, $exception->getRequest());
    }

    /**
     * @test
     */
    public function shouldAllowSetResponse()
    {
        $exception = new HttpException();

        $exception->setResponse(new Response());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetResponse()
    {
        $exception = new HttpException();

        $exception->setResponse($expectedResponse = new Response());

        $this->assertSame($expectedResponse, $exception->getResponse());
    }

    /**
     * @test
     */
    public function shouldAllowFactoryHttpExceptionFromRequestAndResponse()
    {
        $request = new Request();
        $request->setHost('http://example.com');
        $request->setResource('/foobar');

        $response = new Response();
        $response->setHeaders(array('HTTP/1.1 404 Not Found'));

        $httpException = HttpException::factory($request, $response);

        $this->assertInstanceOf('Payum\Core\Exception\Http\HttpException', $httpException);
        $this->assertSame($request, $httpException->getRequest());
        $this->assertSame($response, $httpException->getResponse());

        $this->assertEquals(
            "Client error response\n[status code] 404\n[reason phrase] Not Found\n[url] http://example.com/foobar",
            $httpException->getMessage()
        );
        $this->assertEquals(404, $httpException->getCode());
    }
}
