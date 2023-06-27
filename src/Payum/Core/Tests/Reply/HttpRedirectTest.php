<?php

namespace Payum\Core\Tests\Reply;

use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HttpRedirectTest extends TestCase
{
    public function testShouldReplyInterface(): void
    {
        $rc = new ReflectionClass(HttpRedirect::class);

        $this->assertTrue($rc->implementsInterface(ReplyInterface::class));
    }

    public function testShouldBeSubClassOfHttpResponseClass(): void
    {
        $rc = new ReflectionClass(HttpRedirect::class);

        $this->assertTrue($rc->isSubclassOf(HttpResponse::class));
    }

    public function testShouldAllowGetUrlSetInConstructor(): void
    {
        $expectedUrl = 'theUrl';

        $request = new HttpRedirect($expectedUrl);

        $this->assertSame($expectedUrl, $request->getUrl());
    }

    public function testShouldAllowGetContext(): void
    {
        $expectedContent = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="1;url=foo" />

        <title>Redirecting to foo</title>
    </head>
    <body>
        Redirecting to foo.
    </body>
</html>
HTML;

        $request = new HttpRedirect('foo');

        $this->assertSame($expectedContent, $request->getContent());
    }

    public function testShouldAllowGetDefaultStatusCodeSetInConstructor(): void
    {
        $request = new HttpRedirect('anUrl');

        $this->assertSame(302, $request->getStatusCode());
    }

    public function testShouldAllowGetCustomStatusCodeSetInConstructor(): void
    {
        $request = new HttpRedirect('anUrl', 301);

        $this->assertSame(301, $request->getStatusCode());
    }

    public function testShouldAllowGetDefaultHeadersSetInConstructor(): void
    {
        $request = new HttpRedirect('anUrl');

        $this->assertEquals([
            'Location' => 'anUrl',
        ], $request->getHeaders());
    }

    public function testShouldAllowGetCustomHeadersSetInConstructor(): void
    {
        $customHeaders = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];

        $expectedHeaders = $customHeaders;
        $expectedHeaders['Location'] = 'anUrl';

        $request = new HttpRedirect('anUrl', 302, $customHeaders);

        $this->assertEquals($expectedHeaders, $request->getHeaders());
    }
}
