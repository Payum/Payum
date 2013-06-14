<?php
namespace Payum\Payex\Tests\Action;

use Payum\Payex\Api\OrderApi;
use Payum\PaymentInterface;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Payex\Action\StatusAction;
use Payum\Payex\Model\PaymentDetails;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\StatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new StatusAction;
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayAccessAsModel()
    {
        $action = new StatusAction();

        $this->assertTrue($action->supports(new BinaryMaskStatusRequest($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldSupportBinaryMaskStatusRequestWithPaymentDetailsAsModel()
    {
        $action = new StatusAction;
        
        $this->assertTrue($action->supports(new BinaryMaskStatusRequest(new PaymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
    {
        $action = new StatusAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNotArrayAccessModel()
    {
        $action = new StatusAction;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new StatusAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfOrderStatusNotSupported()
    {
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'orderStatus' => 'not-supported-status'
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'transactionStatus' => 'not-supported-status'
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array());

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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_AUTHORIZE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => 'not-authorize-status',
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => 'not-sale-status',
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_CANCEL,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_CANCEL,
            'errorDetails' => array(
                'transactionErrorCode' => OrderApi::TRANSACTIONERRORCODE_OPERATIONCANCELLEDBYCUSTOMER
            ),
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_FAILURE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'orderStatus' => OrderApi::ORDERSTATUS_PROCESSING
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'orderStatus' => OrderApi::ORDERSTATUS_NOT_FOUND
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'errorCode' => 'not-ok'
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
        $action = new StatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'errorCode' => OrderApi::ERRORCODE_OK,
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isSuccess());
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}