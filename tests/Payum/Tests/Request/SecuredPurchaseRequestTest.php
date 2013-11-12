<?php
namespace Payum\Tests\Request;

use Payum\Request\SecuredPurchaseRequest;
use Payum\Model\Token;

class SecuredPurchaseRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPurchaseRequest()
    {
        $rc = new \ReflectionClass('Payum\Request\SecuredPurchaseRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\PurchaseRequest'));
    }

    /**
     * @test
     */
    public function shouldImplementsSecuredRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\SecuredPurchaseRequest');

        $this->assertTrue($rc->implementsInterface('Payum\Request\SecuredRequestInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTokenAsFirstArgument()
    {
        new SecuredPurchaseRequest($this->getMock('Payum\Security\TokenInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowGetTokenSetInConstructor()
    {
        $expectedToken = new Token;
        
        $request = new SecuredPurchaseRequest($expectedToken);
        
        $this->assertSame($expectedToken, $request->getToken());
        $this->assertSame($expectedToken, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowSetModelAndKeepTokenSame()
    {
        $token = new Token;

        $request = new SecuredPurchaseRequest($token);

        //guard
        $this->assertSame($token, $request->getToken());
        $this->assertSame($token, $request->getModel());

        $newModel = new \stdClass;
            
        $request->setModel($newModel);

        $this->assertSame($token, $request->getToken());
        $this->assertSame($newModel, $request->getModel());
    }
}