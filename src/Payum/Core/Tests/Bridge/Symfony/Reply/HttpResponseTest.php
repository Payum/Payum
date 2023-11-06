<?php
namespace Payum\Core\Tests\Bridge\Symfony\Reply;

use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class HttpResponseTest extends TestCase
{
    public function testShouldBeSubClassOfBaseReply()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Reply\HttpResponse');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Reply\Base'));
    }

    public function testShouldAllowGetResponseSetInConstructor()
    {
        $expectedResponse = new Response();

        $request = new HttpResponse($expectedResponse);

        $this->assertSame($expectedResponse, $request->getResponse());
    }
}
