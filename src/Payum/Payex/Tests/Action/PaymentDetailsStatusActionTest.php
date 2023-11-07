<?php

namespace Payum\Payex\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHumanStatus;
use Payum\Payex\Action\PaymentDetailsStatusAction;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\RecurringApi;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class PaymentDetailsStatusActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(PaymentDetailsStatusAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldSupportGetStatusRequestWithEmptyArrayAsModel(): void
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus([])));
    }

    public function testShouldNotSupportGetStatusRequestWithArrayAsModelIfAutoPaySet(): void
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetHumanStatus([
            'autoPay' => true,
        ])));
    }

    public function testShouldSupportGetStatusRequestWithArrayAsModelIfAutoPaySetToFalse(): void
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus([
            'autoPay' => false,
        ])));
    }

    public function testShouldSupportGetStatusRequestWithArrayAsModelIfRecurringSetToTrueAndAutoPaySet(): void
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus([
            'autoPay' => true,
            'recurring' => true,
        ])));
    }

    public function testShouldNotSupportAnythingNotStatusRequest(): void
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportStatusRequestWithNotArrayAccessModel(): void
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetHumanStatus(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new PaymentDetailsStatusAction();

        $action->execute(new stdClass());
    }

    public function testShouldMarkNewIfDetailsEmpty(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkUnknownIfOrderStatusNotSupported(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'orderStatus' => 'not-supported-status',
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markNew();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkUnknownIfOrderStatusSupportedButTransactionStatusNotSupported(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'transactionStatus' => 'not-supported-status',
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markNew();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkNewIfOrderStatusNotSet(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkCapturedTwoPhaseTransaction(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_AUTHORIZE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkFailedTwoPhaseTransactionIfTransactionStatusNotAuthorize(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => 'not-authorize-status',
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkCapturedOnePhaseTransaction(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkFailedOnePhaseTransactionIfTransactionStatusNotSale(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => 'not-sale-status',
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkCanceledIfTransactionStatusCanceled(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_CANCEL,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkCanceledIfTransactionStatusFailedButErrorDetailsTellCanceled(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_CANCEL,
            'errorDetails' => [
                'transactionErrorCode' => OrderApi::TRANSACTIONERRORCODE_OPERATIONCANCELLEDBYCUSTOMER,
            ],
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkFailedIfTransactionStatusFailed(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_FAILURE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkPendingIfOrderStatusProgressing(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'orderStatus' => OrderApi::ORDERSTATUS_PROCESSING,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isPending());
    }

    public function testShouldMarkExpiredIfOrderStatusNotFound(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'orderStatus' => OrderApi::ORDERSTATUS_NOT_FOUND,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isExpired());
    }

    public function testShouldMarkFailedIfErrorCodeNotOk(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'errorCode' => 'not-ok',
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkCapturedIfErrorCodeOk(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'errorCode' => OrderApi::ERRORCODE_OK,
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkCanceledIfRecurringStatusIsStoppedByMerchant(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYMERCHANT,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkCanceledIfRecurringStatusIsStoppedByAdmin(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYADMIN,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkCanceledIfRecurringStatusIsStoppedByClient(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYCLIENT,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkCanceledIfRecurringStatusIsStoppedBySystem(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYSYSTEM,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkFailedIfRecurringStatusIsFailed(): void
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_FAILED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }
}
