<?php
namespace Payum\Payex\Tests\Action;

use Payum\Payex\Api\RecurringApi;
use Payum\Core\Request\GetHumanStatus;
use Payum\Payex\Action\PaymentDetailsStatusAction;
use Payum\Payex\Api\OrderApi;

class PaymentDetailsStatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\PaymentDetailsStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentDetailsStatusAction();
    }

    /**
     * @test
     */
    public function shouldSupportGetStatusRequestWithEmptyArrayAsModel()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportGetStatusRequestWithArrayAsModelIfAutoPaySet()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetHumanStatus(array(
            'autoPay' => true,
        ))));
    }

    /**
     * @test
     */
    public function shouldSupportGetStatusRequestWithArrayAsModelIfAutoPaySetToFalse()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus(array(
            'autoPay' => false,
        ))));
    }

    /**
     * @test
     */
    public function shouldSupportGetStatusRequestWithArrayAsModelIfRecurringSetToTrueAndAutoPaySet()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus(array(
            'autoPay' => true,
            'recurring' => true,
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNotArrayAccessModel()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetHumanStatus(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new PaymentDetailsStatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array());

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfOrderStatusNotSupported()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'orderStatus' => 'not-supported-status',
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markNew();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfOrderStatusSupportedButTransactionStatusNotSupported()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'transactionStatus' => 'not-supported-status',
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markNew();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfOrderStatusNotSet()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedTwoPhaseTransaction()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_AUTHORIZE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkFailedTwoPhaseTransactionIfTransactionStatusNotAuthorize()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => 'not-authorize-status',
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedOnePhaseTransaction()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkFailedOnePhaseTransactionIfTransactionStatusNotSale()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => 'not-sale-status',
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfTransactionStatusCanceled()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_CANCEL,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfTransactionStatusFailedButErrorDetailsTellCanceled()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_CANCEL,
            'errorDetails' => array(
                'transactionErrorCode' => OrderApi::TRANSACTIONERRORCODE_OPERATIONCANCELLEDBYCUSTOMER,
            ),
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfTransactionStatusFailed()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_FAILURE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfOrderStatusProgressing()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'orderStatus' => OrderApi::ORDERSTATUS_PROCESSING,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkExpiredIfOrderStatusNotFound()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'orderStatus' => OrderApi::ORDERSTATUS_NOT_FOUND,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isExpired());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfErrorCodeNotOk()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'errorCode' => 'not-ok',
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfErrorCodeOk()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'errorCode' => OrderApi::ERRORCODE_OK,
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfRecurringStatusIsStoppedByMerchant()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYMERCHANT,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfRecurringStatusIsStoppedByAdmin()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYADMIN,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfRecurringStatusIsStoppedByClient()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYCLIENT,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfRecurringStatusIsStoppedBySystem()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYSYSTEM,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfRecurringStatusIsFailed()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus(array(
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_FAILED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }
}
