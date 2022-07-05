<?php

namespace Payum\Payex\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Payex\Action\AgreementDetailsStatusAction;
use Payum\Payex\Api\AgreementApi;

class AgreementDetailsStatusActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(AgreementDetailsStatusAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldSupportStatusRequestWithArrayAccessAsModelIfOrderIdNotSetAndAgreementRefSet()
    {
        $action = new AgreementDetailsStatusAction();

        $array = $this->createMock(\ArrayAccess::class);
        $array
            ->expects($this->atLeast(2))
            ->method('offsetExists')
            ->withConsecutive(['agreementRef'], ['orderId'])
            ->willReturnOnConsecutiveCalls(true, false)
        ;

        $this->assertTrue($action->supports(new GetBinaryStatus($array)));
    }

    public function testShouldNotSupportStatusRequestWithArrayAccessAsModelIfOrderIdAndAgreementRefSet()
    {
        $action = new AgreementDetailsStatusAction();

        $array = $this->createMock(\ArrayAccess::class);
        $array
            ->expects($this->atLeast(2))
            ->method('offsetExists')
            ->withConsecutive(['agreementRef'], ['orderId'])
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

        $status = new GetBinaryStatus([
            'agreementRef' => 'aRef',
        ]);

        //guard
        $status->markCaptured();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkNewIfAgreementStatusNotVerified()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new GetBinaryStatus([
            'agreementRef' => 'aRef',
            'agreementStatus' => AgreementApi::AGREEMENTSTATUS_NOTVERIFIED,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkCapturedIfAgreementStatusVerified()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new GetBinaryStatus([
            'agreementRef' => 'aRef',
            'agreementStatus' => AgreementApi::AGREEMENTSTATUS_VERIFIED,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkCanceledIfAgreementStatusDeleted()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new GetBinaryStatus([
            'agreementRef' => 'aRef',
            'agreementStatus' => AgreementApi::AGREEMENTSTATUS_DELETED,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkFailedIfErrorCodeNotOk()
    {
        $action = new AgreementDetailsStatusAction();

        $status = new GetBinaryStatus([
            'agreementRef' => 'aRef',
            'errorCode' => 'not-ok',
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(\Payum\Core\GatewayInterface::class);
    }
}
