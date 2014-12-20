<?php
namespace Payum\Core\Tests\Request\Http;

use Payum\Core\Reply\HttpResponse;

class HttpResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementReplyInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\HttpResponse');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Reply\ReplyInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithContentAsArgument()
    {
        new HttpResponse('html page');
    }

    /**
     * @test
     */
    public function shouldAllowGetContentSetInConstructor()
    {
        $expectedContent = 'html page';

        $request = new HttpResponse($expectedContent);

        $this->assertEquals($expectedContent, $request->getContent());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultStatusCodeSetInConstructor()
    {
        $request = new HttpResponse('html page');

        $this->assertEquals(200, $request->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldAllowGetCustomStatusCodeSetInConstructor()
    {
        $request = new HttpResponse('html page', 301);

        $this->assertEquals(301, $request->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultHeadersSetInConstructor()
    {
        $request = new HttpResponse('html page');

        $this->assertEquals(array(), $request->getHeaders());
    }

    /**
     * @test
     */
    public function shouldAllowGetCustomHeadersSetInConstructor()
    {
        $expectedHeaders = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );

        $request = new HttpResponse('html page', 200, $expectedHeaders);

        $this->assertEquals($expectedHeaders, $request->getHeaders());
    }
}
