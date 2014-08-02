<?php
namespace Payum\Core\Tests\Request\Http;

use Payum\Core\Request\GetHttpRequest;

class GetRequestRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new \Payum\Core\Request\GetHttpRequest;
    }

    /**
     * @test
     */
    public function shouldSetExpectedDefaultValuesInConstructor()
    {
        $request = new \Payum\Core\Request\GetHttpRequest;

        $this->assertSame(array(), $request->query);
        $this->assertSame(array(), $request->request);
        $this->assertSame('', $request->method);
        $this->assertSame('', $request->uri);
    }
}
