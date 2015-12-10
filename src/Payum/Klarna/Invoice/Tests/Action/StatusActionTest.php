<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Klarna\Invoice\Action\StatusAction;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\StatusAction');

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
    public function shouldSupportGetStatusWithArrayAsModel()
    {
        $action = new StatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetStatus()
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportGetStatusWithNotArrayAccessModel()
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new GetHumanStatus(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkAsNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array()));

        $this->assertTrue($getStatus->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkAsNew()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array()));

        $this->assertTrue($getStatus->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfErrorCodeSet()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'error_code' => 'aCode',
        )));

        $this->assertTrue($getStatus->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfCanceledPropertySet()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'canceled' => true,
        )));

        $this->assertTrue($getStatus->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfInvoiceNumberSet()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'invoice_number' => 'aNumber',
        )));

        $this->assertTrue($getStatus->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkAuthorizedIfStatusAccepted()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'status' => \KlarnaFlags::ACCEPTED,
        )));

        $this->assertTrue($getStatus->isAuthorized());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfStatusPending()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'status' => \KlarnaFlags::PENDING,
        )));

        $this->assertTrue($getStatus->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfStatusDenied()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'status' => \KlarnaFlags::DENIED,
        )));

        $this->assertTrue($getStatus->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfStatusUnknown()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'status' => 'unknown',
        )));

        $this->assertTrue($getStatus->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkRefundedIfRefundInvoiceNumberSet()
    {
        $action = new StatusAction();

        $action->execute($getStatus = new GetHumanStatus(array(
            'refund_invoice_number' => 'aNum',
        )));

        $this->assertTrue($getStatus->isRefunded());
    }
}
