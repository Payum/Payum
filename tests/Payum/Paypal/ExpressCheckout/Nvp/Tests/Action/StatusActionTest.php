<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Paypal\ExpressCheckout\Nvp\Action\StatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Request\BinaryMaskStatusRequest;

use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\ModelWithInstruction;

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
    public function shouldSupportStatusRequestWithModelAggregatesPaypalInstruction()
    {
        $action = new StatusAction();
        
        $instruction = new PaymentInstruction();
        
        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $this->assertTrue($action->supports(new BinaryMaskStatusRequest($model)));
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
        $instruction = new PaymentInstruction();
        $instruction->setLErrorcoden(0, Api::L_ERRORCODE_PAYMENT_NOT_AUTHORIZED);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));
        
        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfPayerIdNotSetAndCheckoutStatusNotInitiated()
    {
        $instruction = new PaymentInstruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);
        $instruction->setPayerid(null);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfPayerIdSetAndCheckoutStatusNotInitiated()
    {
        $instruction = new PaymentInstruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);
        $instruction->setPayerid('thePayerId');

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkInProgressIfCheckoutStatusInProgress()
    {
        $instruction = new PaymentInstruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isInProgress());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfCheckoutStatusFailed()
    {
        $instruction = new PaymentInstruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_FAILED);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkInProgressIfAtLeastOnePaymentStatusInProgress()
    {
        $instruction = new PaymentInstruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $instruction->setPaymentrequestPaymentstatus(0, Api::PAYMENTSTATUS_COMPLETED);
        $instruction->setPaymentrequestPaymentstatus(1, Api::PAYMENTSTATUS_IN_PROGRESS);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isInProgress());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfAtLeastOnePaymentStatusFailed()
    {
        $instruction = new PaymentInstruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $instruction->setPaymentrequestPaymentstatus(0, Api::PAYMENTSTATUS_COMPLETED);
        $instruction->setPaymentrequestPaymentstatus(1, Api::PAYMENTSTATUS_FAILED);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessIfAllPaymentStatusCompletedOrProcessed()
    {
        $instruction = new PaymentInstruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $instruction->setPaymentrequestPaymentstatus(0, Api::PAYMENTSTATUS_COMPLETED);
        $instruction->setPaymentrequestPaymentstatus(1, Api::PAYMENTSTATUS_PROCESSED);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);

        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfCheckoutStatusUnknown()
    {
        $instruction = new PaymentInstruction();
        $instruction->setCheckoutstatus('unknowCheckoutStatus');

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfPaymentStatusUnknown()
    {
        $instruction = new PaymentInstruction();
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $instruction->setPaymentrequestPaymentstatus(0, 'unknownPaymentStatus');

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isUnknown());
    }
}