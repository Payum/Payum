<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class RecurringPaymentDetailsStatusActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldSupportStatusRequestWithArrayAsModelWhichHasBillingPeriodSet()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $recurringPaymentDetails = array(
           'BILLINGPERIOD' => 'foo',
        );

        $request = new GetBinaryStatus($recurringPaymentDetails);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportStatusRequestWithNoArrayAccessAsModel()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotStatusRequest()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new RecurringPaymentDetailsStatusAction();

        $action->execute(new \stdClass());
    }

    public function testShouldMarkFailedIfErrorCodeSetToModel()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'L_ERRORCODE9' => 'foo',
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkNewIfProfileStatusAndStatusNotSet()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
        ));

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkUnknownIfProfileStatusAndStatusNotRecognized()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => 'foo',
            'PROFILESTATUS' => 'bar',
        ));

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldStatusHasGreaterPriorityOverProfileStatus()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_EXPIRED,
            'PROFILESTATUS' => Api::PROFILESTATUS_PENDINGPROFILE,
        ));

        $action->execute($request);

        $this->assertTrue($request->isExpired());
    }

    public function testShouldMarkPendingIfProfileStatusPendingAndStatusNotSet()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'PROFILESTATUS' => Api::PROFILESTATUS_PENDINGPROFILE,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkCapturedIfProfileStatusActiveAndStatusNotSet()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'PROFILESTATUS' => Api::PROFILESTATUS_ACTIVEPROFILE,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkCapturedIfStatusActive()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_ACTIVE,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkCanceledIfStatusCanceled()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_CANCELLED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkPendingIfStatusPending()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_PENDING,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkExpiredIfStatusExpired()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_EXPIRED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isExpired());
    }

    public function testShouldMarkSuspendedIfStatusSuspended()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_SUSPENDED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isSuspended());
    }
}
