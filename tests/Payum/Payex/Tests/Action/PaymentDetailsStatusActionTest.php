<?php
namespace Payum\Payex\Tests\Action;

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
    public function shouldSupportStatusRequestWithArrayAccessAsModelWithOrderIdSet()
    {
        $action = new PaymentDetailsStatusAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('orderId')
            ->will($this->returnValue(true))
        ;
        
        $this->assertTrue($action->supports(new BinaryMaskStatusRequest($array)));
    }
    
    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithArrayAccessAsModelIfOrderIdNotSet()
    {
        $action = new PaymentDetailsStatusAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('orderId')
            ->will($this->returnValue(false))
        ;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest($array)));
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