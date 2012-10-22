<?php
namespace Payum\Tests\Exception\Http;

use Buzz\Message\Request;
use Buzz\Message\Response;

use Payum\Exception\Http\HttpResponseStatusNotSuccessfulException;

class HttpResponseStatusNotSuccessfulExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfHttpException()
    {
        $rc = new \ReflectionClass('Payum\Exception\Http\HttpResponseStatusNotSuccessfulException');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Exception\Http\HttpException'));
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultMessageIfNotSetInConstructor()
    {
        $exception = new HttpResponseStatusNotSuccessfulException(new Request, new Response);
        
        $this->assertEquals('The response `` status is not success.', $exception->getMessage());
    }

    /**
     * @test
     */
    public function shouldAllowGetMessageSetInConstructor()
    {
        $expectedMessage = 'theMessage';
        
        $exception = new HttpResponseStatusNotSuccessfulException(new Request, new Response, $expectedMessage);

        $this->assertEquals($expectedMessage, $exception->getMessage());
    }
}
