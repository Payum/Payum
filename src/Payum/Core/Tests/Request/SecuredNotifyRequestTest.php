<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\SecuredNotifyRequest;
use Payum\Core\Model\Token;

class SecuredNotifyRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfCaptureRequest()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\SecuredNotifyRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\NotifyRequest'));
    }

    /**
     * @test
     */
    public function shouldImplementsSecuredRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\SecuredNotifyRequest');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\SecuredRequestInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithNotificationAndTokenAsModel()
    {
        new SecuredNotifyRequest(array(), $this->getMock('Payum\Core\Security\TokenInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowGetTokenSetInConstructor()
    {
        $expectedToken = new Token;
        
        $request = new SecuredNotifyRequest($notification = array(), $expectedToken);
        
        $this->assertSame($expectedToken, $request->getToken());
        $this->assertSame($expectedToken, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowSetModelAndKeepTokenSame()
    {
        $token = new Token;

        $request = new SecuredNotifyRequest($notification = array(), $token);

        //guard
        $this->assertSame($token, $request->getToken());
        $this->assertSame($token, $request->getModel());

        $newModel = new \stdClass;
            
        $request->setModel($newModel);

        $this->assertSame($token, $request->getToken());
        $this->assertSame($newModel, $request->getModel());
    }
}