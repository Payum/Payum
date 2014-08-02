<?php
namespace Payum\Core\Tests\Request\Http;

use Payum\Core\Reply\HttpPostRedirect;

class PostRedirectUrlInteractiveRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInteractiveRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\Http\PostRedirectUrlInteractiveRequest');
        
        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\InteractiveRequestInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfResponseInteractiveRequest()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\Http\PostRedirectUrlInteractiveRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Http\ResponseInteractiveRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithUrlAsArgument()
    {
        new \Payum\Core\Reply\HttpPostRedirect('an_url');
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
        
        $request = new \Payum\Core\Reply\HttpPostRedirect('theUrl');
        
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

        $request = new \Payum\Core\Reply\HttpPostRedirect('theUrl', array('foo' => 'fooVal', 'bar' => 'barVal'));

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