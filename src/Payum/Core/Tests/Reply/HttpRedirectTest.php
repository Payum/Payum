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
}