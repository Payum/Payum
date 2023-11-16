<?php

namespace Payum\Core\Tests\Reply;

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HttpResponseTest extends TestCase
{
    public function testShouldImplementReplyInterface(): void
    {
        $rc = new ReflectionClass(HttpResponse::class);

        $this->assertTrue($rc->implementsInterface(ReplyInterface::class));
    }

    public function testShouldAllowGetContentSetInConstructor(): void
    {
        $expectedContent = 'html page';

        $request = new HttpResponse($expectedContent);

        $this->assertSame($expectedContent, $request->getContent());
    }

    public function testShouldAllowGetDefaultStatusCodeSetInConstructor(): void
    {
        $request = new HttpResponse('html page');

        $this->assertSame(200, $request->getStatusCode());
    }

    public function testShouldAllowGetCustomStatusCodeSetInConstructor(): void
    {
        $request = new HttpResponse('html page', 301);

        $this->assertSame(301, $request->getStatusCode());
    }

    public function testShouldAllowGetDefaultHeadersSetInConstructor(): void
    {
        $request = new HttpResponse('html page');

        $this->assertSame([], $request->getHeaders());
    }

    public function testShouldAllowGetCustomHeadersSetInConstructor(): void
    {
        $expectedHeaders = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];

        $request = new HttpResponse('html page', 200, $expectedHeaders);

        $this->assertSame($expectedHeaders, $request->getHeaders());
    }
}
