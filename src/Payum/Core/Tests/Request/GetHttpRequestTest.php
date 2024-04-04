<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetHttpRequest;
use PHPUnit\Framework\TestCase;

class GetHttpRequestTest extends TestCase
{

    public function testShouldSetExpectedDefaultValuesInConstructor()
    {
        $request = new GetHttpRequest();

        $this->assertSame([], $request->query);
        $this->assertSame([], $request->request);
        $this->assertSame('', $request->method);
        $this->assertSame('', $request->uri);
    }
}
