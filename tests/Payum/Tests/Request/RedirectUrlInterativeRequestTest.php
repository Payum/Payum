<?php
namespace Payum\Tests\Request;

use Payum\Request\RedirectUrlInteractiveRequest;

class RedirectUrlInteractiveRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInteractiveRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\RedirectUrlInteractiveRequest');
        
        $this->assertTrue($rc->implementsInterface('Payum\Request\InteractiveRequestInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithUrlAsArgument()
    {
        new RedirectUrlInteractiveRequest('an_url');
    }

    /**
     * @test
     */
    public function shouldAllowGetUrlSetInConstructor()
    {
        $expectedUrl = 'theUrl';
        
        $request = new RedirectUrlInteractiveRequest($expectedUrl);
        
        $this->assertEquals($expectedUrl, $request->getUrl());
    }
}