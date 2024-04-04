<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class PaymentDetailsStatusActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSet()
    {
        $action = new PaymentDetailsStatusAction();

        $payment = array(
           'PAYMENTREQUEST_0_AMT' => 1,
        );

        $request = new GetHumanStatus($payment);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldSupportEmptyModel()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array());

        $this->assertTrue($action->supports($request));
    }

    public function testShouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSetToZero()
    {
        $action = new PaymentDetailsStatusAction();

        $payment = array(
            'PAYMENTREQUEST_0_AMT' => 0,
        );

        $request = new GetHumanStatus($payment);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportStatusRequestWithNoArrayAccessAsModel()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotStatusRequest()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new PaymentDetailsStatusAction();

        $action->execute(new \stdClass());
    }

    public function testShouldMarkCanceledIfPaymentNotAuthorized()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'L_ERRORCODE0' => Api::L_ERRORCODE_PAYMENT_NOT_AUTHORIZED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkCanceledIfDetailsContainCanceledKey()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'CANCELLED' => true,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkFailedIfErrorCodeSetToModel()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 21,
            'L_ERRORCODE9' => 'foo'
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkCapturedIfCreateBillingAgreementRequestAndZeroAmount()
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

    public function testShouldMarkNewIfDetailsEmpty()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array());

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkNewIfPayerIdSetAndCheckoutStatusNotInitiated()
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

    public function testShouldMarkPendingIfCheckoutStatusInProgress()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkPendingIfPayerIdNotSetAndCheckoutStatusNotInitiated()
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

    public function testShouldMarkFailedIfCheckoutStatusFailed()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_FAILED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkPendingIfAtLeastOnePaymentStatusInProgress()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_IN_PROGRESS,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkFailedIfAtLeastOnePaymentStatusFailed()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_FAILED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkFailedIfAtLeastOnePaymentStatusReversed()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_REVERSED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkRefundedIfAtLeastOnePaymentStatusRefund()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_REFUNDED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkRefundedIfAtLeastOnePaymentStatusPartiallyRefund()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PARTIALLY_REFUNDED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkCapturedIfAllPaymentStatusCompletedOrProcessed()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PROCESSED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkCanceledIfAllPaymentStatusVoidedAndReasonAuthorization()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_VOIDED,
            'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_VOIDED,
            'PAYMENTINFO_9_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkAuthorizedIfAllPaymentStatusPendingAndReasonAuthorization()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
            'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
            'PAYMENTINFO_9_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,

        ));

        $action->execute($request);

        $this->assertTrue($request->isAuthorized());
    }

    public function testShouldMarkUnknownIfCheckoutStatusUnknown()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => 'unknownCheckoutStatus',
        ));

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkUnknownIfPaymentStatusUnknown()
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => 'unknownPaymentStatus',
        ));

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }


    public function testShouldMarkCanceledIfPaymentIsCancelledByUser()
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
