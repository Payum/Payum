<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\ResponseInteractiveRequest;

class ResponseInteractiveRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInteractiveRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\ResponseInteractiveRequest');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\InteractiveRequestInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithUrlAsArgument()
    {
        new ResponseInteractiveRequest('html page');
    }

    /**
     * @test
     */
    public function shouldAllowGetUrlSetInConstructor()
    {
        $expectedContent = 'html page';
        
        $request = new ResponseInteractiveRequest($expectedContent);
        
        $this->assertEquals($expectedContent, $request->getContent());
    }
}