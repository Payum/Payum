<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetHttpRequest;

class GetHttpRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new GetHttpRequest();
    }

    /**
     * @test
     */
    public function shouldSetExpectedDefaultValuesInConstructor()
    {
        $request = new GetHttpRequest();

        $this->assertSame([], $request->query);
        $this->assertSame([], $request->request);
        $this->assertSame('', $request->method);
        $this->assertSame('', $request->uri);
    }
}
