<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\RedirectUrlInteractiveRequest;

class RedirectUrlInteractiveRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInteractiveRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\RedirectUrlInteractiveRequest');
        
        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\InteractiveRequestInterface'));
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