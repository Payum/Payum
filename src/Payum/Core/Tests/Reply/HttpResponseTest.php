<?php
namespace Payum\Core\Tests\Reply;

use Payum\Core\Reply\HttpResponse;
use PHPUnit\Framework\TestCase;

class HttpResponseTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementReplyInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\HttpResponse');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Reply\ReplyInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowGetContentSetInConstructor()
    {
        $expectedContent = 'html page';

        $request = new HttpResponse($expectedContent);

        $this->assertSame($expectedContent, $request->getContent());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultStatusCodeSetInConstructor()
    {
        $request = new HttpResponse('html page');

        $this->assertSame(200, $request->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldAllowGetCustomStatusCodeSetInConstructor()
    {
        $request = new HttpResponse('html page', 301);

        $this->assertSame(301, $request->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultHeadersSetInConstructor()
    {
        $request = new HttpResponse('html page');

        $this->assertSame(array(), $request->getHeaders());
    }

    /**
     * @test
     */
    public function shouldAllowGetCustomHeadersSetInConstructor()
    {
        $expectedHeaders = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );

        $request = new HttpResponse('html page', 200, $expectedHeaders);

        $this->assertSame($expectedHeaders, $request->getHeaders());
    }
}
