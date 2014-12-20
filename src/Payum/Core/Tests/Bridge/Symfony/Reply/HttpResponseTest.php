<?php
namespace Payum\Core\Tests\Bridge\Symfony\Reply;

use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class HttpResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseReply()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Reply\HttpResponse');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Reply\Base'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithResponseAsFirstArgument()
    {
        new HttpResponse(new Response());
    }

    /**
     * @test
     */
    public function shouldAllowGetResponseSetInConstructor()
    {
        $expectedResponse = new Response();

        $request = new HttpResponse($expectedResponse);

        $this->assertSame($expectedResponse, $request->getResponse());
    }
}
