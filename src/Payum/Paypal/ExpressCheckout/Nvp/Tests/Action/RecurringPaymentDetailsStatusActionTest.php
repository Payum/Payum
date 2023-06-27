<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class RecurringPaymentDetailsStatusActionTest extends TestCase
{
    public function testShouldImplementsActionInterface(): void
    {
        $rc = new ReflectionClass(RecurringPaymentDetailsStatusAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportStatusRequestWithArrayAsModelWhichHasBillingPeriodSet(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $recurringPaymentDetails = [
            'BILLINGPERIOD' => 'foo',
        ];

        $request = new GetBinaryStatus($recurringPaymentDetails);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportStatusRequestWithNoArrayAccessAsModel(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(new stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotStatusRequest(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new RecurringPaymentDetailsStatusAction();

        $action->execute(new stdClass());
    }

    public function testShouldMarkFailedIfErrorCodeSetToModel(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
            'L_ERRORCODE9' => 'foo',
        ]);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkNewIfProfileStatusAndStatusNotSet(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
        ]);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkUnknownIfProfileStatusAndStatusNotRecognized(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
            'STATUS' => 'foo',
            'PROFILESTATUS' => 'bar',
        ]);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldStatusHasGreaterPriorityOverProfileStatus(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_EXPIRED,
            'PROFILESTATUS' => Api::PROFILESTATUS_PENDINGPROFILE,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isExpired());
    }

    public function testShouldMarkPendingIfProfileStatusPendingAndStatusNotSet(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
            'PROFILESTATUS' => Api::PROFILESTATUS_PENDINGPROFILE,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkCapturedIfProfileStatusActiveAndStatusNotSet(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
            'PROFILESTATUS' => Api::PROFILESTATUS_ACTIVEPROFILE,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkCapturedIfStatusActive(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_ACTIVE,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkCanceledIfStatusCanceled(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_CANCELLED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkPendingIfStatusPending(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_PENDING,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkExpiredIfStatusExpired(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_EXPIRED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isExpired());
    }

    public function testShouldMarkSuspendedIfStatusSuspended(): void
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus([
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_SUSPENDED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isSuspended());
    }
}
