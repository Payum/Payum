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
    public function shouldSupportStatusRequestWithPaymentInstructionAsModel()
    {
        $action = new StatusAction();
        
        $request = new BinaryMaskStatusRequest(new PaymentInstruction());
        
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestPaymentInstructionAggregate()
    {
        $action = new StatusAction();

        $model = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $model
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue(new PaymentInstruction))
        ;

        $request = new BinaryMaskStatusRequest($model);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNoPaymentInstructionAsModel()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotStatusRequest()
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
        $model = new PaymentInstruction();
        $model->setLErrorcoden(0, Api::L_ERRORCODE_PAYMENT_NOT_AUTHORIZED);

        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));
        
        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfPayerIdNotSetAndCheckoutStatusNotInitiated()
    {
        $model = new PaymentInstruction();
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);
        $model->setPayerid(null);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfPayerIdSetAndCheckoutStatusNotInitiated()
    {
        $model = new PaymentInstruction();
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);
        $model->setPayerid('thePayerId');
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkInProgressIfCheckoutStatusInProgress()
    {
        $model = new PaymentInstruction();
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isInProgress());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfCheckoutStatusFailed()
    {
        $model = new PaymentInstruction();
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_FAILED);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkInProgressIfAtLeastOnePaymentStatusInProgress()
    {
        $model = new PaymentInstruction();
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $model->setPaymentrequestPaymentstatus(0, Api::PAYMENTSTATUS_COMPLETED);
        $model->setPaymentrequestPaymentstatus(1, Api::PAYMENTSTATUS_IN_PROGRESS);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isInProgress());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfAtLeastOnePaymentStatusFailed()
    {
        $model = new PaymentInstruction();
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $model->setPaymentrequestPaymentstatus(0, Api::PAYMENTSTATUS_COMPLETED);
        $model->setPaymentrequestPaymentstatus(1, Api::PAYMENTSTATUS_FAILED);
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessIfAllPaymentStatusCompletedOrProcessed()
    {
        $model = new PaymentInstruction();
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $model->setPaymentrequestPaymentstatus(0, Api::PAYMENTSTATUS_COMPLETED);
        $model->setPaymentrequestPaymentstatus(1, Api::PAYMENTSTATUS_PROCESSED);

        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfCheckoutStatusUnknown()
    {
        $model = new PaymentInstruction();
        $model->setCheckoutstatus('unknowCheckoutStatus');
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfPaymentStatusUnknown()
    {
        $model = new PaymentInstruction();
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_COMPLETED);
        $model->setPaymentrequestPaymentstatus(0, 'unknownPaymentStatus');
        
        $action = new StatusAction();

        $action->execute($request = new BinaryMaskStatusRequest($model));

        $this->assertTrue($request->isUnknown());
    }
}