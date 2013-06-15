<?php
namespace Payum\Payex\Tests\Action;

use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Model\AgreementDetails;
use Payum\PaymentInterface;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Payex\Action\AgreementDetailsStatusAction;
use Payum\Payex\Model\PaymentDetails;

class AgreementDetailsStatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\AgreementDetailsStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AgreementDetailsStatusAction;
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayAccessAsModelWithMerchantRefSet()
    {
        $action = new AgreementDetailsStatusAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('merchantRef')
            ->will($this->returnValue(true))
        ;
        
        $this->assertTrue($action->supports(new BinaryMaskStatusRequest($array)));
    }
    
    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithArrayAccessAsModelIfMerchantRefNotSet()
    {
        $action = new AgreementDetailsStatusAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('merchantRef')
            ->will($this->returnValue(false))
        ;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest($array)));
    }

    /**
     * @test
     */
    public function shouldSupportBinaryMaskStatusRequestWithAgreementDetailsAsModel()
    {
        $action = new AgreementDetailsStatusAction;

        $this->assertTrue($action->supports(new BinaryMaskStatusRequest(new AgreementDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportBinaryMaskStatusRequestWithPaymentDetailsAsModel()
    {
        $action = new AgreementDetailsStatusAction;
        
        $this->assertFalse($action->supports(new BinaryMaskStatusRequest(new PaymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
    {
        $action = new AgreementDetailsStatusAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNotArrayAccessModel()
    {
        $action = new AgreementDetailsStatusAction;

        $this->assertFalse($action->supports(new BinaryMaskStatusRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new AgreementDetailsStatusAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfTransactionStatusNotSet()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'merchantRef' => 'aRef',
        ));

        //guard
        $status->markSuccess();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfAgreementStatusNotVerified()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'merchantRef' => 'aRef',
            'agreementStatus' => AgreementApi::AGREEMENTSTATUS_NOTVERIFIED,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessIfAgreementStatusVerified()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'merchantRef' => 'aRef',
            'agreementStatus' => AgreementApi::AGREEMENTSTATUS_VERIFIED,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfAgreementStatusDeleted()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'merchantRef' => 'aRef',
            'agreementStatus' => AgreementApi::AGREEMENTSTATUS_DELETED,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfErrorCodeNotOk()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new BinaryMaskStatusRequest(array(
            'errorCode' => 'not-ok',
            'merchantRef' => 'aRef',
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