<?php
namespace Payum\Core\Tests\Bridge\Symfony\Request;

use Payum\Core\Bridge\Symfony\Request\ResponseInteractiveRequest;
use Symfony\Component\HttpFoundation\Response;

class ResponseInteractiveRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseInteractiveRequest()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Request\ResponseInteractiveRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseInteractiveRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithResponseAsFirstArgument()
    {
        new ResponseInteractiveRequest(new Response);
    }

    /**
     * @test
     */
    public function shouldAllowGetResponseSetInConstructor()
    {
        $expectedResponse = new Response;

        $request = new ResponseInteractiveRequest($expectedResponse);

        $this->assertSame($expectedResponse, $request->getResponse());
    }
}
