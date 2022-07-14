<?php

namespace Payum\Klarna\Invoice\Tests\Action;

use KlarnaFlags;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHumanStatus;
use Payum\Klarna\Invoice\Action\StatusAction;
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

    public function testShouldSupportGetStatusWithArrayAsModel(): void
    {
        $action = new StatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus([])));
    }

    public function testShouldNotSupportAnythingNotGetStatus(): void
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportGetStatusWithNotArrayAccessModel(): void
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new GetHumanStatus(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new StatusAction();

        $action->execute(new stdClass());
    }

    public function testShouldMarkAsNewIfDetailsEmpty(): void
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus([]));

        $this->assertTrue($getStatus->isNew());
    }

    public function testShouldMarkAsNew(): void
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus([]));

        $this->assertTrue($getStatus->isNew());
    }

    public function testShouldMarkFailedIfErrorCodeSet(): void
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus([
            'error_code' => 'aCode',
        ]));

        $this->assertTrue($getStatus->isFailed());
    }

    public function testShouldMarkCanceledIfCanceledPropertySet(): void
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus([
            'canceled' => true,
        ]));

        $this->assertTrue($getStatus->isCanceled());
    }

    public function testShouldMarkCapturedIfInvoiceNumberSet(): void
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus([
            'invoice_number' => 'aNumber',
        ]));

        $this->assertTrue($getStatus->isCaptured());
    }

    public function testShouldMarkAuthorizedIfStatusAccepted(): void
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus([
            'status' => KlarnaFlags::ACCEPTED,
        ]));

        $this->assertTrue($getStatus->isAuthorized());
    }

    public function testShouldMarkPendingIfStatusPending(): void
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus([
            'status' => KlarnaFlags::PENDING,
        ]));

        $this->assertTrue($getStatus->isPending());
    }

    public function testShouldMarkFailedIfStatusDenied(): void
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus([
            'status' => KlarnaFlags::DENIED,
        ]));

        $this->assertTrue($getStatus->isFailed());
    }

    public function testShouldMarkUnknownIfStatusUnknown(): void
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus([
            'status' => 'unknown',
        ]));

        $this->assertTrue($getStatus->isUnknown());
    }

    public function testShouldMarkRefundedIfRefundInvoiceNumberSet(): void
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus([
            'refund_invoice_number' => 'aNum',
        ]));

        $this->assertTrue($getStatus->isRefunded());
    }
}
