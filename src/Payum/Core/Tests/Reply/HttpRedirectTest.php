<?php
namespace Payum\Core\Tests\Reply;

use Payum\Core\Reply\HttpRedirect;

class HttpRedirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldReplyInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\HttpRedirect');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Reply\ReplyInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfHttpResponseClass()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\HttpRedirect');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Reply\HttpResponse'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithUrlAsArgument()
    {
        new HttpRedirect('an_url');
    }

    /**
     * @test
     */
    public function shouldAllowGetUrlSetInConstructor()
    {
        $expectedUrl = 'theUrl';

        $request = new HttpRedirect($expectedUrl);

        $this->assertEquals($expectedUrl, $request->getUrl());
    }

    /**
     * @test
     */
    public function shouldAllowGetContext()
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

        $this->assertEquals($expectedContent, $request->getContent());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultStatusCodeSetInConstructor()
    {
        $request = new HttpRedirect('anUrl');

        $this->assertEquals(302, $request->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldAllowGetCustomStatusCodeSetInConstructor()
    {
        $request = new HttpRedirect('anUrl', 301);

        $this->assertEquals(301, $request->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultHeadersSetInConstructor()
    {
        $request = new HttpRedirect('anUrl');

        $this->assertEquals(array(
            'Location' => 'anUrl',
        ), $request->getHeaders());
    }

    /**
     * @test
     */
    public function shouldAllowGetCustomHeadersSetInConstructor()
    {
        $customHeaders = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );

        $expectedHeaders = $customHeaders;
        $expectedHeaders['Location'] = 'anUrl';

        $request = new HttpRedirect('anUrl', 302, $customHeaders);

        $this->assertEquals($expectedHeaders, $request->getHeaders());
    }
}
