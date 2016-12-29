<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class PaymentDetailsStatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentDetailsStatusAction();
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSet()
    {
        $action = new PaymentDetailsStatusAction();

        $payment = array(
           'PAYMENTREQUEST_0_AMT' => 1,
        );

        $request = new GetHumanStatus($payment);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportEmptyModel()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array());

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSetToZero()
    {
        $action = new PaymentDetailsStatusAction();

        $payment = array(
            'PAYMENTREQUEST_0_AMT' => 0,
        );

        $request = new GetHumanStatus($payment);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNoArrayAccessAsModel()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new PaymentDetailsStatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfPaymentNotAuthorized()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'L_ERRORCODE0' => Api::L_ERRORCODE_PAYMENT_NOT_AUTHORIZED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfDetailsContainCanceledKey()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'CANCELLED' => true,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfErrorCodeSetToModel()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 21,
            'L_ERRORCODE9' => 'foo'
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfCreateBillingAgreementRequestAndZeroAmount()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 0,
            'PAYERID' => 'thePayerId',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
            'L_BILLINGTYPE0' => Api::BILLINGTYPE_RECURRING_PAYMENTS,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array());

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfPayerIdSetAndCheckoutStatusNotInitiated()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 0,
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
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfPayerIdNotSetAndCheckoutStatusNotInitiated()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'PAYERID' => null,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfCheckoutStatusFailed()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_FAILED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfAtLeastOnePaymentStatusInProgress()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
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
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
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
    public function shouldMarkFailedIfAtLeastOnePaymentStatusReversed()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_REVERSED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkRefundedIfAtLeastOnePaymentStatusRefund()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_REFUNDED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    /**
     * @test
     */
    public function shouldMarkRefundedIfAtLeastOnePaymentStatusPartiallyRefund()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PARTIALLY_REFUNDED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfAllPaymentStatusCompletedOrProcessed()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PROCESSED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfAllPaymentStatusVoidedAndReasonAuthorization()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_VOIDED,
            'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_VOIDED,
            'PAYMENTINFO_9_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkAuthorizedIfAllPaymentStatusPendingAndReasonAuthorization()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
            'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
            'PAYMENTINFO_9_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,

        ));

        $action->execute($request);

        $this->assertTrue($request->isAuthorized());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfCheckoutStatusUnknown()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
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
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTREQUEST_9_PAYMENTSTATUS' => 'unknownPaymentStatus',
        ));

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }


    /**
     * @test
     */
    public function shouldMarkCanceledIfPaymentIsCancelledByUser()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
            'CANCELLED' => true,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }
}
