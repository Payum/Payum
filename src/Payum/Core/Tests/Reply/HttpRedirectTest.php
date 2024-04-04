<?php
namespace Payum\Core\Tests\Reply;

use Payum\Core\Reply\HttpRedirect;
use PHPUnit\Framework\TestCase;

class HttpRedirectTest extends TestCase
{
    public function testShouldReplyInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\HttpRedirect');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Reply\ReplyInterface'));
    }

    public function testShouldBeSubClassOfHttpResponseClass()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\HttpRedirect');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Reply\HttpResponse'));
    }

    public function testShouldAllowGetUrlSetInConstructor()
    {
        $expectedUrl = 'theUrl';

        $request = new HttpRedirect($expectedUrl);

        $this->assertSame($expectedUrl, $request->getUrl());
    }

    public function testShouldAllowGetContext()
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

    public function testShouldAllowGetDefaultStatusCodeSetInConstructor()
    {
        $request = new HttpRedirect('anUrl');

        $this->assertSame(302, $request->getStatusCode());
    }

    public function testShouldAllowGetCustomStatusCodeSetInConstructor()
    {
        $request = new HttpRedirect('anUrl', 301);

        $this->assertSame(301, $request->getStatusCode());
    }

    public function testShouldAllowGetDefaultHeadersSetInConstructor()
    {
        $request = new HttpRedirect('anUrl');

        $this->assertSame(array(
            'Location' => 'anUrl',
        ), $request->getHeaders());
    }

    public function testShouldAllowGetCustomHeadersSetInConstructor()
    {
        $customHeaders = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );

        $expectedHeaders = $customHeaders;
        $expectedHeaders['Location'] = 'anUrl';

        $request = new HttpRedirect('anUrl', 302, $customHeaders);

        $this->assertSame($expectedHeaders, $request->getHeaders());
    }
}
