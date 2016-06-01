<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ProHosted\Nvp\Action\StatusAction;
use Payum\Paypal\ProHosted\Nvp\Api;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ProHosted\Nvp\Action\StatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
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
    public function shouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSet()
    {
        $action = new StatusAction();

        $payment = array(
           'AMT' => 1,
        );

        $request = new GetHumanStatus($payment);

        $this->assertNotFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportEmptyModel()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array());

        $this->assertNotFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSetToZero()
    {
        $action = new StatusAction();

        $payment = array(
            'AMT' => 0,
        );

        $request = new GetHumanStatus($payment);

        $this->assertNotFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNoArrayAccessAsModel()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(new \stdClass());

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
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfDetailsContainCanceledKey()
    {
        $action = new StatusAction();

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
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT'          => 21,
            'L_ERRORCODE0' => 'foo',
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array());

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfPaymentStatusNotSet()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 0,
            'PAYERID' => 'thePayerId',
            'PAYMENTSTATUS' => '',
        ));

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfPaymentStatusPending()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfPaymentStatusFailed()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_FAILED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkRefundedIfPaymentStatusRefund()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_REFUNDED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    /**
     * @test
     */
    public function shouldMarkRefundedIfPaymentStatusPartiallyRefund()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_PARTIALLY_REFUNDED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfPaymentStatusCompleted()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkAuthorizedIfPaymentStatusPendingAndReasonAuthorization()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
            'PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,

        ));

        $action->execute($request);

        $this->assertTrue($request->isAuthorized());
    }
}
