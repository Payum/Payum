<?php
namespace Payum\Core\Tests\Reply;

use Payum\Core\Reply\HttpResponse;
use PHPUnit\Framework\TestCase;

class HttpResponseTest extends TestCase
{
    public function testShouldImplementReplyInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\HttpResponse');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Reply\ReplyInterface'));
    }

    public function testShouldAllowGetContentSetInConstructor()
    {
        $expectedContent = 'html page';

        $request = new HttpResponse($expectedContent);

        $this->assertSame($expectedContent, $request->getContent());
    }

    public function testShouldAllowGetDefaultStatusCodeSetInConstructor()
    {
        $request = new HttpResponse('html page');

        $this->assertSame(200, $request->getStatusCode());
    }

    public function testShouldAllowGetCustomStatusCodeSetInConstructor()
    {
        $request = new HttpResponse('html page', 301);

        $this->assertSame(301, $request->getStatusCode());
    }

    public function testShouldAllowGetDefaultHeadersSetInConstructor()
    {
        $request = new HttpResponse('html page');

        $this->assertSame(array(), $request->getHeaders());
    }

    public function testShouldAllowGetCustomHeadersSetInConstructor()
    {
        $expectedHeaders = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );

        $request = new HttpResponse('html page', 200, $expectedHeaders);

        $this->assertSame($expectedHeaders, $request->getHeaders());
    }
}
