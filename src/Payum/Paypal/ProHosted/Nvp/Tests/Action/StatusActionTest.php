<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ProHosted\Nvp\Action\StatusAction;
use Payum\Paypal\ProHosted\Nvp\Api;

class StatusActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ProHosted\Nvp\Action\StatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSet()
    {
        $action = new StatusAction();

        $payment = array(
           'AMT' => 1,
        );

        $request = new GetHumanStatus($payment);

        $this->assertNotFalse($action->supports($request));
    }

    public function testShouldSupportEmptyModel()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array());

        $this->assertNotFalse($action->supports($request));
    }

    public function testShouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSetToZero()
    {
        $action = new StatusAction();

        $payment = array(
            'AMT' => 0,
        );

        $request = new GetHumanStatus($payment);

        $this->assertNotFalse($action->supports($request));
    }

    public function testShouldNotSupportStatusRequestWithNoArrayAccessAsModel()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotStatusRequest()
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

    public function testShouldMarkCanceledIfDetailsContainCanceledKey()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'CANCELLED' => true,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkFailedIfErrorCodeSetToModel()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT'          => 21,
            'L_ERRORCODE0' => 'foo',
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array());

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkUnknownIfPaymentStatusNotSet()
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

    public function testShouldMarkPendingIfPaymentStatusPending()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
        ));

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkFailedIfPaymentStatusFailed()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_FAILED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkRefundedIfPaymentStatusRefund()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_REFUNDED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkRefundedIfPaymentStatusPartiallyRefund()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_PARTIALLY_REFUNDED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkCapturedIfPaymentStatusCompleted()
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(array(
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
        ));

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkAuthorizedIfPaymentStatusPendingAndReasonAuthorization()
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
