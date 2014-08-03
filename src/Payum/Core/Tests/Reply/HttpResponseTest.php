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
    public function couldBeConstructedWithUrlAsArgument()
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
}