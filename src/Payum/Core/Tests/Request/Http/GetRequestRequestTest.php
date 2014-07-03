<?php
namespace Payum\Core\Tests\Request\Http;

use Payum\Core\Request\Http\GetRequestRequest;

class GetRequestRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new GetRequestRequest;
    }

    /**
     * @test
     */
    public function shouldSetExpectedDefaultValuesInConstructor()
    {
        $request = new GetRequestRequest;

        $this->assertSame(array(), $request->query);
        $this->assertSame(array(), $request->request);
        $this->assertSame('', $request->method);
        $this->assertSame('', $request->uri);
    }
}
