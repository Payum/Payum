<?php
namespace Payum\Payex\Tests\Action;

use Payum\Payex\Api\AgreementApi;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Payex\Action\AgreementDetailsStatusAction;

class AgreementDetailsStatusActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\AgreementDetailsStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayAccessAsModelIfOrderIdNotSetAndAgreementRefSet()
    {
        $action = new AgreementDetailsStatusAction();

        $array = $this->createMock('ArrayAccess');
        $array
            ->expects($this->at(0))
            ->method('offsetExists')
            ->with('agreementRef')
            ->willReturn(true)
        ;
        $array
            ->expects($this->at(1))
            ->method('offsetExists')
            ->with('orderId')
            ->willReturn(false)
        ;

        $this->assertTrue($action->supports(new GetBinaryStatus($array)));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithArrayAccessAsModelIfOrderIdAndAgreementRefSet()
    {
        $action = new AgreementDetailsStatusAction();

        $array = $this->createMock('ArrayAccess');
        $array
            ->expects($this->at(0))
            ->method('offsetExists')
            ->with('agreementRef')
            ->willReturn(true)
        ;
        $array
            ->expects($this->at(1))
            ->method('offsetExists')
            ->with('orderId')
            ->willReturn(true)
        ;

        $this->assertFalse($action->supports(new GetBinaryStatus($array)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
    {
        $action = new AgreementDetailsStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNotArrayAccessModel()
    {
        $action = new AgreementDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new AgreementDetailsStatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfTransactionStatusNotSet()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new GetBinaryStatus(array(
            'agreementRef' => 'aRef',
        ));

        //guard
        $status->markCaptured();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfAgreementStatusNotVerified()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new GetBinaryStatus(array(
            'agreementRef' => 'aRef',
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
    public function shouldMarkCapturedIfAgreementStatusVerified()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new GetBinaryStatus(array(
            'agreementRef' => 'aRef',
            'agreementStatus' => AgreementApi::AGREEMENTSTATUS_VERIFIED,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfAgreementStatusDeleted()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new GetBinaryStatus(array(
            'agreementRef' => 'aRef',
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

        $status = new GetBinaryStatus(array(
            'agreementRef' => 'aRef',
            'errorCode' => 'not-ok',
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
