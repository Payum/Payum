<?php

namespace Payum\Payex\Tests\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Payex\Action\AgreementDetailsStatusAction;
use Payum\Payex\Api\AgreementApi;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AgreementDetailsStatusActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(AgreementDetailsStatusAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldSupportStatusRequestWithArrayAccessAsModelIfOrderIdNotSetAndAgreementRefSet(): void
    {
        $action = new AgreementDetailsStatusAction();

        $array = $this->createMock(ArrayAccess::class);
        $array
            ->expects($this->atLeast(2))
            ->method('offsetExists')
            ->withConsecutive(['agreementRef'], ['orderId'])
            ->willReturnOnConsecutiveCalls(true, false)
        ;

        $this->assertTrue($action->supports(new GetBinaryStatus($array)));
    }

    public function testShouldNotSupportStatusRequestWithArrayAccessAsModelIfOrderIdAndAgreementRefSet(): void
    {
        $action = new AgreementDetailsStatusAction();

        $array = $this->createMock(ArrayAccess::class);
        $array
            ->expects($this->atLeast(2))
            ->method('offsetExists')
            ->withConsecutive(['agreementRef'], ['orderId'])
            ->willReturn(true)
        ;

        $this->assertFalse($action->supports(new GetBinaryStatus($array)));
    }

    public function testShouldNotSupportAnythingNotStatusRequest(): void
    {
        $action = new AgreementDetailsStatusAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportStatusRequestWithNotArrayAccessModel(): void
    {
        $action = new AgreementDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new AgreementDetailsStatusAction();

        $action->execute(new stdClass());
    }

    public function testShouldMarkUnknownIfTransactionStatusNotSet(): void
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

    public function testShouldMarkNewIfAgreementStatusNotVerified(): void
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

    public function testShouldMarkCapturedIfAgreementStatusVerified(): void
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

    public function testShouldMarkCanceledIfAgreementStatusDeleted(): void
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

    public function testShouldMarkFailedIfErrorCodeNotOk(): void
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
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
