<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ProHosted\Nvp\Action\StatusAction;
use Payum\Paypal\ProHosted\Nvp\Api;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class StatusActionTest extends TestCase
{
    public function testShouldImplementsActionInterface(): void
    {
        $rc = new ReflectionClass(StatusAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSet(): void
    {
        $action = new StatusAction();

        $payment = [
            'AMT' => 1,
        ];

        $request = new GetHumanStatus($payment);

        $this->assertNotFalse($action->supports($request));
    }

    public function testShouldSupportEmptyModel(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([]);

        $this->assertNotFalse($action->supports($request));
    }

    public function testShouldSupportStatusRequestWithArrayAsModelWhichHasPaymentRequestAmountSetToZero(): void
    {
        $action = new StatusAction();

        $payment = [
            'AMT' => 0,
        ];

        $request = new GetHumanStatus($payment);

        $this->assertNotFalse($action->supports($request));
    }

    public function testShouldNotSupportStatusRequestWithNoArrayAccessAsModel(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus(new stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotStatusRequest(): void
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new StatusAction();

        $action->execute(new stdClass());
    }

    public function testShouldMarkCanceledIfDetailsContainCanceledKey(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([
            'CANCELLED' => true,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkFailedIfErrorCodeSetToModel(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([
            'AMT' => 21,
            'L_ERRORCODE0' => 'foo',
        ]);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkNewIfDetailsEmpty(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([]);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkUnknownIfPaymentStatusNotSet(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([
            'AMT' => 0,
            'PAYERID' => 'thePayerId',
            'PAYMENTSTATUS' => '',
        ]);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkPendingIfPaymentStatusPending(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkFailedIfPaymentStatusFailed(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_FAILED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkRefundedIfPaymentStatusRefund(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_REFUNDED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkRefundedIfPaymentStatusPartiallyRefund(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_PARTIALLY_REFUNDED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkCapturedIfPaymentStatusCompleted(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_COMPLETED,
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkAuthorizedIfPaymentStatusPendingAndReasonAuthorization(): void
    {
        $action = new StatusAction();

        $request = new GetHumanStatus([
            'AMT' => 12,
            'PAYMENTSTATUS' => Api::PAYMENTSTATUS_PENDING,
            'PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,

        ]);

        $action->execute($request);

        $this->assertTrue($request->isAuthorized());
    }
}
