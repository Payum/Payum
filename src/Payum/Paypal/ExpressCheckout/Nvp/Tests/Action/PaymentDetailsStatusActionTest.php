<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class PaymentDetailsStatusActionTest extends TestCase
{
    public function testShouldImplementsActionInterface(): void
    {
        $rc = new ReflectionClass(PaymentDetailsStatusAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSet(): void
    {
        $action = new PaymentDetailsStatusAction();

        $payment = [
            'PAYMENTREQUEST_0_AMT' => 1,
        ];

        $request = new GetHumanStatus($payment);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldSupportEmptyModel(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([]);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSetToZero(): void
    {
        $action = new PaymentDetailsStatusAction();

        $payment = [
            'PAYMENTREQUEST_0_AMT' => 0,
        ];

        $request = new GetHumanStatus($payment);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportStatusRequestWithNoArrayAccessAsModel(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus(new stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotStatusRequest(): void
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new PaymentDetailsStatusAction();

        $action->execute(new stdClass());
    }

    public function testShouldMarkCanceledIfPaymentNotAuthorized(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'L_ERRORCODE0' => Api::L_ERRORCODE_PAYMENT_NOT_AUTHORIZED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkCanceledIfDetailsContainCanceledKey(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'CANCELLED' => true,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkFailedIfErrorCodeSetToModel(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 21,
            'L_ERRORCODE9' => 'foo',
        ]);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkCapturedIfCreateBillingAgreementRequestAndZeroAmount(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 0,
            'PAYERID' => 'thePayerId',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
            'L_BILLINGTYPE0' => Api::BILLINGTYPE_RECURRING_PAYMENTS,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkNewIfDetailsEmpty(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([]);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkNewIfPayerIdSetAndCheckoutStatusNotInitiated(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 0,
            'PAYERID' => 'thePayerId',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkPendingIfCheckoutStatusInProgress(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkPendingIfPayerIdNotSetAndCheckoutStatusNotInitiated(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'PAYERID' => null,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkFailedIfCheckoutStatusFailed(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_FAILED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkPendingIfAtLeastOnePaymentStatusInProgress(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_IN_PROGRESS,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkFailedIfAtLeastOnePaymentStatusFailed(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_FAILED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkFailedIfAtLeastOnePaymentStatusReversed(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_REVERSED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkRefundedIfAtLeastOnePaymentStatusRefund(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_REFUNDED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkRefundedIfAtLeastOnePaymentStatusPartiallyRefund(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PARTIALLY_REFUNDED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkCapturedIfAllPaymentStatusCompletedOrProcessed(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PROCESSED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkCanceledIfAllPaymentStatusVoidedAndReasonAuthorization(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_VOIDED,
            'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_VOIDED,
            'PAYMENTINFO_9_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkAuthorizedIfAllPaymentStatusPendingAndReasonAuthorization(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_0_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
            'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
            'PAYMENTINFO_9_PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
            'PAYMENTINFO_9_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,

        ]);

        $action->execute($request);

        $this->assertTrue($request->isAuthorized());
    }

    public function testShouldMarkUnknownIfCheckoutStatusUnknown(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => 'unknownCheckoutStatus',
        ]);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkUnknownIfPaymentStatusUnknown(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'PAYMENTREQUEST_0_AMT' => 12,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_COMPLETED,
            'PAYMENTINFO_9_PAYMENTSTATUS' => 'unknownPaymentStatus',
        ]);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkCanceledIfPaymentIsCancelledByUser(): void
    {
        $action = new PaymentDetailsStatusAction();

        $request = new GetHumanStatus([
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
            'CANCELLED' => true,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }
}
