<?php
namespace Payum\Payex\Tests\Action;

use Payum\PaymentInterface;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Payex\Action\AutoPayPaymentDetailsStatusAction;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Model\PaymentDetails;
use Payum\Payex\Model\AgreementDetails;

class AutoPayPaymentDetailsStatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\AutoPayPaymentDetailsStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AutoPayPaymentDetailsStatusAction;
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayAccessAsModelWithAutoPaySet()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('autoPay')
            ->will($this->returnValue(true))
        ;
        $array
            ->expects($this->once())
            ->method('offsetGet')
            ->with('autoPay')
            ->will($this->returnValue(true))
        ;
        
        $this->assertTrue($action->supports(new BinaryMaskStatusRequest($array)));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithArrayAccessAsModelWithAutoPaySetToFalse()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('autoPay')
            ->will($this->returnValue(true))
        ;
        $array
            ->expects($this->once())
            ->method('offsetGet')
            ->with('autoPay')
            ->will($this->returnValue(false))
        ;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest($array)));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithArrayAccessAsModelWithAutoPayNotSet()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('autoPay')
            ->will($this->returnValue(false))
        ;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest($array)));
    }

    /**
     * @test
     */
    public function shouldSupportBinaryMaskStatusRequestWithPaymentDetailsAsModel()
    {
        $action = new AutoPayPaymentDetailsStatusAction;

        $details = new PaymentDetails;
        $details->setAutoPay(true);
        
        $this->assertTrue($action->supports(new BinaryMaskStatusRequest($details)));
    }

    /**
     * @test
     */
    public function shouldNotSupportBinaryMaskStatusRequestWithAgreementDetailsAsModel()
    {
        $action = new AutoPayPaymentDetailsStatusAction;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest(new AgreementDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
    {
        $action = new AutoPayPaymentDetailsStatusAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNotArrayAccessModel()
    {
        $action = new AutoPayPaymentDetailsStatusAction;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new AutoPayPaymentDetailsStatusAction;

        $action->execute(new \stdClass());
    }


    /**
     * @test
     */
    public function shouldMarkNewIfTransactionStatusNotSet()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'autoPay' => true,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessIfPurchaseOperationAuthorizeAndTransactionStatusThree()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_AUTHORIZE,
            'autoPay' => true,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessIfPurchaseOperationSaleAndTransactionStatusZero()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'autoPay' => true,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfTransactionStatusNeitherZeroOrThree()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => 'foobarbaz',
            'autoPay' => true,
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