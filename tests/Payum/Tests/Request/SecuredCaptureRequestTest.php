<?php
namespace Payum\Tests\Request;

use Payum\Request\SecuredCaptureRequest;
use Payum\Model\Token;

class SecuredCaptureRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfCaptureRequest()
    {
        $rc = new \ReflectionClass('Payum\Request\SecuredCaptureRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\CaptureRequest'));
    }

    /**
     * @test
     */
    public function shouldImplementsSecuredRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\SecuredCaptureRequest');

        $this->assertTrue($rc->implementsInterface('Payum\Request\SecuredRequestInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTokenAsFirstArgument()
    {
        new SecuredCaptureRequest($this->getMock('Payum\Security\TokenInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowGetTokenSetInConstructor()
    {
        $expectedToken = new Token;
        
        $request = new SecuredCaptureRequest($expectedToken);
        
        $this->assertSame($expectedToken, $request->getToken());
        $this->assertSame($expectedToken, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowSetModelAndKeepTokenSame()
    {
        $token = new Token;

        $request = new SecuredCaptureRequest($token);

        //guard
        $this->assertSame($token, $request->getToken());
        $this->assertSame($token, $request->getModel());

        $newModel = new \stdClass;
            
        $request->setModel($newModel);

        $this->assertSame($token, $request->getToken());
        $this->assertSame($newModel, $request->getModel());
    }
}