<?php
namespace Payum\Core\Tests\Reply;

use Payum\Core\Reply\HttpPostRedirect;

class HttpPostRedirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementReplyInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\HttpPostRedirect');
        
        $this->assertTrue($rc->implementsInterface('Payum\Core\Reply\ReplyInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfHttpResponseReply()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\HttpPostRedirect');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Reply\HttpResponse'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithUrlAsArgument()
    {
        new HttpPostRedirect('an_url');
    }

    /**
     * @test
     */
    public function couldBeConstructedWithUrlAsArgumentAndPostValuesArray()
    {
        new HttpPostRedirect('an_url', array('foo' => 'bar'));
    }

    /**
     * @test
     */
    public function shouldAllowGetContentWhenPostNotSet()
    {
        $expectedContent = <<<'HTML'
<!DOCTYPE html>
<html>
    <head>
        <title>Redirecting...</title>
    </head>
    <body onload="document.forms[0].submit();">
        <form action="theUrl" method="post">
            <p>Redirecting to payment page...</p>
            <p><input type="submit" value="Continue" /></p>
        </form>
    </body>
</html>
HTML;
        
        $request = new HttpPostRedirect('theUrl');
        
        $this->assertEquals($expectedContent, $request->getContent());
    }

    /**
     * @test
     */
    public function shouldAllowGetContentWhenPostSet()
    {
        $expectedContent = <<<'HTML'
<!DOCTYPE html>
<html>
    <head>
        <title>Redirecting...</title>
    </head>
    <body onload="document.forms[0].submit();">
        <form action="theUrl" method="post">
            <p>Redirecting to payment page...</p>
            <p><input type="hidden" name="foo" value="fooVal" />
<input type="hidden" name="bar" value="barVal" />
<input type="submit" value="Continue" /></p>
        </form>
    </body>
</html>
HTML;

        $request = new HttpPostRedirect('theUrl', array('foo' => 'fooVal', 'bar' => 'barVal'));

        $this->assertEquals($expectedContent, $request->getContent());
    }

    /**
     * @test
     */
    public function shouldEscapeHtmlSpecialChars()
    {
        $expectedContent = <<<'HTML'
<!DOCTYPE html>
<html>
    <head>
        <title>Redirecting...</title>
    </head>
    <body onload="document.forms[0].submit();">
        <form action="theUrl" method="post">
            <p>Redirecting to payment page...</p>
            <p><input type="hidden" name="foo" value="&lt;&gt;&amp;&quot;" />
<input type="submit" value="Continue" /></p>
        </form>
    </body>
</html>
HTML;

        $request = new HttpPostRedirect('theUrl', array('foo' => '<>&"'));

        $this->assertEquals($expectedContent, $request->getContent());
    }
}