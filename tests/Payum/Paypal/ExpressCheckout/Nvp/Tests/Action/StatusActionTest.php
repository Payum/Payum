<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Request\BinaryMaskStatusRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\StatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

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
    public function shouldSupportStatusRequestWithArrayAccessAsModel()
    {
        $action = new StatusAction();
        
        $request = new BinaryMaskStatusRequest($this->getMock('ArrayAccess'));
        
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithPaymentDetailsAsModel()
    {
        $action = new StatusAction();

        $this->assertTrue($action->supports(new BinaryMaskStatusRequest(new PaymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNoArrayAccessAsModel()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
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
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'L_ERRORCODE0' => Api::L_ERRORCODE_PAYMENT_NOT_AUTHORIZED
        ));
        
        $action->execute($request);
        
        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfErrorCodeSetToModel()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'L_ERRORCODE9' => 'foo'
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfPayerIdNotSetAndCheckoutStatusNotInitiated()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'PAYERID' => null,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessIfCreateBillingAgreementRequestAndZeroAmount()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'PAYERID' => 'thePayerId',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
            'L_BILLINGTYPE0' => Api::BILLINGTYPE_RECURRING_PAYMENTS,
            'PAYMENTREQUEST_0_AMT' => 0,
        ));

        $action->execute($request);

        $this->assertTrue($request->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfPayerIdSetAndCheckoutStatusNotInitiated()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'PAYERID' => 'thePayerId',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfCheckoutStatusInProgress()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfCheckoutStatusFailed()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_FAILED
        ));
        
        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfAtLeastOnePaymentStatusInProgress()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_IN_PROGRESS,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfAtLeastOnePaymentStatusFailed()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_FAILED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessIfAllPaymentStatusCompletedOrProcessed()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PROCESSED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfCheckoutStatusUnknown()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'CHECKOUTSTATUS' => 'unknownCheckoutStatus',
        ));

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfPaymentStatusUnknown()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(array(
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => 'unknownPaymentStatus',
        ));

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }
}