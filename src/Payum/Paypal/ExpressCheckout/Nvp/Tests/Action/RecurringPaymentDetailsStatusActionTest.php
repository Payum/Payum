<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class RecurringPaymentDetailsStatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RecurringPaymentDetailsStatusAction();
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayAsModelWhichHasBillingPeriodSet()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $recurringPaymentDetails = array(
           'BILLINGPERIOD' => 'foo',
        );

        $request = new GetBinaryStatus($recurringPaymentDetails);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNoArrayAccessAsModel()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfErrorCodeSetToModel()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'L_ERRORCODE9' => 'foo',
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfProfileStatusAndStatusNotSet()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
        ));

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfProfileStatusAndStatusNotRecognized()
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

    /**
     * @test
     */
    public function shouldStatusHasGreaterPriorityOverProfileStatus()
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

    /**
     * @test
     */
    public function shouldMarkPendingIfProfileStatusPendingAndStatusNotSet()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'PROFILESTATUS' => Api::PROFILESTATUS_PENDINGPROFILE,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfProfileStatusActiveAndStatusNotSet()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'PROFILESTATUS' => Api::PROFILESTATUS_ACTIVEPROFILE,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfStatusActive()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_ACTIVE,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfStatusCanceled()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_CANCELLED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfStatusPending()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_PENDING,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkExpiredIfStatusExpired()
    {
        $action = new RecurringPaymentDetailsStatusAction();

        $request = new GetBinaryStatus(array(
            'BILLINGPERIOD' => 'foo',
            'STATUS' => Api::RECURRINGPAYMENTSTATUS_EXPIRED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isExpired());
    }

    /**
     * @test
     */
    public function shouldMarkSuspendedIfStatusSuspended()
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
