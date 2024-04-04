<?php
namespace Payum\Payex\Tests\Action;

use Payum\Payex\Api\AgreementApi;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Payex\Action\AgreementDetailsStatusAction;

class AgreementDetailsStatusActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\AgreementDetailsStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldSupportStatusRequestWithArrayAccessAsModelIfOrderIdNotSetAndAgreementRefSet()
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

    public function testShouldNotSupportStatusRequestWithArrayAccessAsModelIfOrderIdAndAgreementRefSet()
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

    public function testShouldNotSupportAnythingNotStatusRequest()
    {
        $action = new AgreementDetailsStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportStatusRequestWithNotArrayAccessModel()
    {
        $action = new AgreementDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new AgreementDetailsStatusAction();

        $action->execute(new \stdClass());
    }

    public function testShouldMarkUnknownIfTransactionStatusNotSet()
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

    public function testShouldMarkNewIfAgreementStatusNotVerified()
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

    public function testShouldMarkCapturedIfAgreementStatusVerified()
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

    public function testShouldMarkCanceledIfAgreementStatusDeleted()
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

    public function testShouldMarkFailedIfErrorCodeNotOk()
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
