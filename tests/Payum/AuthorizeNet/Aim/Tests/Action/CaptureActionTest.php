<?php
namespace Payum\AuthorizeNet\Aim\Tests\Action;

use Payum\Request\CaptureRequest;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\AuthorizeNet\Aim\PaymentInstruction;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\Action\CaptureAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new CaptureAction();
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestAndArrayAccessAsModel()
    {
        $action = new CaptureAction();

        $request = new CaptureRequest($this->getMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithPaymentInstructionAsModel()
    {
        $action = new CaptureAction();

        $request = new CaptureRequest(new PaymentInstruction);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureRequest()
    {
        $action = new CaptureAction();
        
        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestAndNotArrayAsModel()
    {
        $action = new CaptureAction();
        
        $request = new CaptureRequest(new \stdClass());
        
        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CaptureAction();

        $action->execute(new \stdClass());
    }
}