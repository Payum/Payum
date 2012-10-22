<?php
namespace Payum\Tests\Exception\Http;

use Buzz\Message\Request;
use Buzz\Message\Response;

use Payum\Exception\Http\HttpException;

class HttpExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfLogicException()
    {
        $rc = new \ReflectionClass('Payum\Exception\Http\HttpException');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Exception\LogicException'));
    }

    /**
     * @test
     */
    public function shouldBeConstructedWithRequestAndResponse()
    {
        new HttpException(new Request, new Response);
    }

    /**
     * @test
     */
    public function shouldAllowGetRequestSetInConstructor()
    {
        $expectedRequest = new Request;
        
        $exception = new HttpException($expectedRequest, new Response);
        
        $this->assertSame($expectedRequest, $exception->getRequest());
    }

    /**
     * @test
     */
    public function shouldAllowGetResponseSetInConstructor()
    {
        $expectedResponse = new Response();

        $exception = new HttpException(new Request, $expectedResponse);

        $this->assertSame($expectedResponse, $exception->getResponse());
    }
}
