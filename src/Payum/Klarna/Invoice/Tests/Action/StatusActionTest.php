<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Klarna\Invoice\Action\StatusAction;
use PHPUnit\Framework\TestCase;

class StatusActionTest extends TestCase
{
    public function testShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\StatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldSupportGetStatusWithArrayAsModel()
    {
        $action = new StatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus(array())));
    }

    public function testShouldNotSupportAnythingNotGetStatus()
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportGetStatusWithNotArrayAccessModel()
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new GetHumanStatus(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

    public function testShouldMarkAsNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array()));

        $this->assertTrue($getStatus->isNew());
    }

    public function testShouldMarkAsNew()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array()));

        $this->assertTrue($getStatus->isNew());
    }

    public function testShouldMarkFailedIfErrorCodeSet()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'error_code' => 'aCode',
        )));

        $this->assertTrue($getStatus->isFailed());
    }

    public function testShouldMarkCanceledIfCanceledPropertySet()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'canceled' => true,
        )));

        $this->assertTrue($getStatus->isCanceled());
    }

    public function testShouldMarkCapturedIfInvoiceNumberSet()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'invoice_number' => 'aNumber',
        )));

        $this->assertTrue($getStatus->isCaptured());
    }

    public function testShouldMarkAuthorizedIfStatusAccepted()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'status' => \KlarnaFlags::ACCEPTED,
        )));

        $this->assertTrue($getStatus->isAuthorized());
    }

    public function testShouldMarkPendingIfStatusPending()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'status' => \KlarnaFlags::PENDING,
        )));

        $this->assertTrue($getStatus->isPending());
    }

    public function testShouldMarkFailedIfStatusDenied()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'status' => \KlarnaFlags::DENIED,
        )));

        $this->assertTrue($getStatus->isFailed());
    }

    public function testShouldMarkUnknownIfStatusUnknown()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'status' => 'unknown',
        )));

        $this->assertTrue($getStatus->isUnknown());
    }

    public function testShouldMarkRefundedIfRefundInvoiceNumberSet()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'refund_invoice_number' => 'aNum',
        )));

        $this->assertTrue($getStatus->isRefunded());
    }
}
