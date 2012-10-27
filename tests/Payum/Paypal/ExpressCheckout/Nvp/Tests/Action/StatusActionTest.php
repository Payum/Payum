<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Paypal\ExpressCheckout\Nvp\Action\StatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Request\BinaryMaskStatusRequest;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\StatusAction');
        
        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new StatusAction();
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithInternalRequestAggregatePaypalExpressCheckoutInstruction()
    {
        $action = new StatusAction();
        
        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub(new Instruction()));
        
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequestInterface()
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfPaymentNotAuthorized()
    {
        $instruction = new Instruction();
        $instruction->setLErrorcoden(0, Api::L_ERRORCODE_PAYMENT_NOT_AUTHORIZED);
        
        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub($instruction));

        $action = new StatusAction();

        $action->execute($request);
        
        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfPayerIdNotSetAndCheckoutStatusNotInitiated()
    {
        $instruction = new Instruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);
        $instruction->setPayerid(null);

        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub($instruction));

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfPayerIdSetAndCheckoutStatusNotInitiated()
    {
        $instruction = new Instruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);
        $instruction->setPayerid('thePayerId');

        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub($instruction));

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkInProgressIfCheckoutStatusInProgress()
    {
        $instruction = new Instruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS);

        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub($instruction));

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isInProgress());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfCheckoutStatusFailed()
    {
        $instruction = new Instruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_FAILED);

        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub($instruction));

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkInProgressIfAtLeastOnePaymentStatusInProgress()
    {
        $instruction = new Instruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $instruction->setPaymentrequestNPaymentstatus(0, Api::PAYMENTSTATUS_COMPLETED);
        $instruction->setPaymentrequestNPaymentstatus(1, Api::PAYMENTSTATUS_IN_PROGRESS);

        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub($instruction));

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isInProgress());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfAtLeastOnePaymentStatusFailed()
    {
        $instruction = new Instruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $instruction->setPaymentrequestNPaymentstatus(0, Api::PAYMENTSTATUS_COMPLETED);
        $instruction->setPaymentrequestNPaymentstatus(1, Api::PAYMENTSTATUS_FAILED);

        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub($instruction));

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessIfAllPaymentStatusCompletedOrProcessed()
    {
        $instruction = new Instruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $instruction->setPaymentrequestNPaymentstatus(0, Api::PAYMENTSTATUS_COMPLETED);
        $instruction->setPaymentrequestNPaymentstatus(1, Api::PAYMENTSTATUS_PROCESSED);

        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub($instruction));

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfCheckoutStatusUnknown()
    {
        $instruction = new Instruction();
        $instruction->setCheckoutstatus('unknowCheckoutStatus');

        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub($instruction));

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfPaymentStatusUnknown()
    {
        $instruction = new Instruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $instruction->setPaymentrequestNPaymentstatus(0, 'unknownPaymentStatus');

        $request = new BinaryMaskStatusRequest($this->createInnerRequestStub($instruction));

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @param Instruction $instruction
     * 
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Request\BaseInstructionRequest
     */
    protected function createInnerRequestStub($instruction)
    {      
        return $this->getMockForAbstractClass('Payum\Paypal\ExpressCheckout\Nvp\Request\BaseInstructionRequest', array($instruction));        
    }
}