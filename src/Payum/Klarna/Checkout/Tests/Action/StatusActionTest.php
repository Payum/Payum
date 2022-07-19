<?php

namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\StatusAction;
use Payum\Klarna\Checkout\Constants;

class StatusActionTest extends GenericActionTest
{
    /**
     * @var class-string<StatusAction>
     */
    protected $actionClass = StatusAction::class;

    /**
     * @var class-string<GetHumanStatus>
     */
    protected $requestClass = GetHumanStatus::class;

    public function testShouldMarkUnknownIfStatusNotSupported(): void
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

    public function testShouldMarkNewIfDetailsEmpty(): void
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkNewIfOrderStatusNotSet(): void
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus([]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkNewIfStatusCheckoutIncomplete(): void
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

    public function testShouldMarkPendingIfStatusCheckoutComplete(): void
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

    public function testShouldMarkAuthorizedIfReservationSet(): void
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

    public function testShouldMarkCapturedIfInvoiceNumberSet(): void
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

    public function testShouldMarkFailedIfErrorCodeSet(): void
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

    public function testShouldMarkFailedEvenIfInvoceNumberAndErrorCodeSet(): void
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

    public function testShouldMarkFailedEvenIfStatusCreatedAndErrorCodeSet(): void
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
