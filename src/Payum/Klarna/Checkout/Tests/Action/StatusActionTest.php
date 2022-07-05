<?php

namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\StatusAction;
use Payum\Klarna\Checkout\Constants;

class StatusActionTest extends GenericActionTest
{
    protected $actionClass = \Payum\Klarna\Checkout\Action\StatusAction::class;

    protected $requestClass = \Payum\Core\Request\GetHumanStatus::class;

    public function testShouldMarkUnknownIfStatusNotSupported()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([
            'status' => 'not-supported-status',
        ]);

        //guard
        $status->markNew();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkNewIfOrderStatusNotSet()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkNewIfStatusCheckoutIncomplete()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([
            'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkPendingIfStatusCheckoutComplete()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([
            'status' => Constants::STATUS_CHECKOUT_COMPLETE,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isPending());
    }

    public function testShouldMarkAuthorizedIfReservationSet()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([
            'reservation' => 'aNumber',
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isAuthorized());
    }

    public function testShouldMarkCapturedIfInvoiceNumberSet()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([
            'invoice_number' => 'aNum',
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkFailedIfErrorCodeSet()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([
            'error_code' => 'aCode',
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkFailedEvenIfInvoceNumberAndErrorCodeSet()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([
            'error_code' => 'aCode',
            'invoice_number' => 'aNum',
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkFailedEvenIfStatusCreatedAndErrorCodeSet()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([
            'error_code' => 'aCode',
            'status' => Constants::STATUS_CREATED,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }
}
