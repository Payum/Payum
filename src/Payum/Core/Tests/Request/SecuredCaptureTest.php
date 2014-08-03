<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\SecuredCapture;
use Payum\Core\Model\Token;

class SecuredCaptureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfCapture()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\SecuredCapture');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Capture'));
    }

    /**
     * @test
     */
    public function shouldImplementsSecuredRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\SecuredCapture');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\SecuredRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTokenAsFirstArgument()
    {
        new SecuredCapture($this->getMock('Payum\Core\Security\TokenInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowGetTokenSetInConstructor()
    {
        $expectedToken = new Token;
        
        $request = new SecuredCapture($expectedToken);
        
        $this->assertSame($expectedToken, $request->getToken());
        $this->assertSame($expectedToken, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowSetModelAndKeepTokenSame()
    {
        $token = new Token;

        $request = new SecuredCapture($token);

        //guard
        $this->assertSame($token, $request->getToken());
        $this->assertSame($token, $request->getModel());

        $newModel = new \stdClass;
            
        $request->setModel($newModel);

        $this->assertSame($token, $request->getToken());
        $this->assertSame($newModel, $request->getModel());
    }
}