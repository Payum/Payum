<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\SecuredAuthorize;
use Payum\Core\Model\Token;

class SecuredAuthorizeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAuthorize()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\SecuredAuthorize');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Authorize'));
    }

    /**
     * @test
     */
    public function shouldImplementsSecuredInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\SecuredAuthorize');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\SecuredInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTokenAsFirstArgument()
    {
        new SecuredAuthorize($this->getMock('Payum\Core\Security\TokenInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowGetTokenSetInConstructor()
    {
        $expectedToken = new Token;
        
        $request = new SecuredAuthorize($expectedToken);
        
        $this->assertSame($expectedToken, $request->getToken());
        $this->assertSame($expectedToken, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowSetModelAndKeepTokenSame()
    {
        $token = new Token;

        $request = new SecuredAuthorize($token);

        //guard
        $this->assertSame($token, $request->getToken());
        $this->assertSame($token, $request->getModel());

        $newModel = new \stdClass;
            
        $request->setModel($newModel);

        $this->assertSame($token, $request->getToken());
        $this->assertSame($newModel, $request->getModel());
    }
}