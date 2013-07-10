<?php
namespace Payum\Payex\Tests\Action;

use Payum\Payex\Api\RecurringApi;
use Payum\PaymentInterface;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Payex\Action\PaymentDetailsStatusAction;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Model\PaymentDetails;
use Payum\Payex\Model\AgreementDetails;

class PaymentDetailsStatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\PaymentDetailsStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentDetailsStatusAction;
    }

    /**
     * @test
     */
    public function shouldNotSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySet()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest(array(
            'autoPay' => true
        ))));
    }

    /**
     * @test
     */
    public function shouldSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySetToFalse()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new BinaryMaskStatusRequest(array(
            'autoPay' => false
        ))));
    }

    /**
     * @test
     */
    public function shouldSupportBinaryMaskStatusRequestWithArrayAsModelIfRecurringSetToTrueAndAutoPaySet()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new BinaryMaskStatusRequest(array(
            'autoPay' => true,
            'recurring' => true
        ))));
    }

    /**
     * @test
     */
    public function shouldSupportBinaryMaskStatusRequestWithPaymentDetailsAsModel()
    {
        $action = new PaymentDetailsStatusAction;

        $this->assertTrue($action->supports(new BinaryMaskStatusRequest(new PaymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotBinaryMaskStatusRequest()
    {
        $action = new PaymentDetailsStatusAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportBinaryMaskStatusRequestWithNotArrayAccessModel()
    {
        $action = new PaymentDetailsStatusAction;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest(new \stdClass)));
    }

    /**
     * @test
     */
    public function shouldNotSupportBinaryMaskStatusRequestWithAgreementDetailsAsModel()
    {
        $action = new PaymentDetailsStatusAction;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest(new AgreementDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
    {
        $action = new PaymentDetailsStatusAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNotArrayAccessModel()
    {
        $action = new PaymentDetailsStatusAction;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new PaymentDetailsStatusAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfOrderStatusNotSupported()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
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
    public function shouldMarkSuccessTwoPhaseTransaction()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_AUTHORIZE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkFailedTwoPhaseTransactionIfTransactionStatusNotAuthorize()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
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
    public function shouldMarkSuccessOnePhaseTransaction()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkFailedOnePhaseTransactionIfTransactionStatusNotSale()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_CANCEL,
            'errorDetails' => array(
                'transactionErrorCode' => OrderApi::TRANSACTIONERRORCODE_OPERATIONCANCELLEDBYCUSTOMER
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

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
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
    public function shouldMarkSuccessIfErrorCodeOk()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
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

        $this->assertTrue($status->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfRecurringStatusIsStoppedByMerchant()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
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

        $status = new BinaryMaskStatusRequest(array(
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_FAILED,
            'orderId' => 'anId',
            'autoPay' => false,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}