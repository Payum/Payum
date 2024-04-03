<?php

namespace Payum\Payex\Tests\Action;

use ArrayAccess;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Payex\Action\AgreementDetailsSyncAction;
use Payum\Payex\Request\Api\CheckAgreement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AgreementDetailsSyncActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(AgreementDetailsSyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncWithArrayAccessAsModelIfOrderIdNotSetAndAgreementRefSet(): void
    {
        $action = new AgreementDetailsSyncAction();

        $array = $this->createMock(ArrayAccess::class);
        $array
            ->expects($this->atLeast(2))
            ->method('offsetExists')
            ->withConsecutive(['agreementRef'], ['orderId'])
            ->willReturnOnConsecutiveCalls(true, false)
        ;

        $this->assertTrue($action->supports(new Sync($array)));
    }

    public function testShouldNotSupportSyncWithArrayAccessAsModelIfOrderIdAndAgreementRefSet(): void
    {
        $action = new AgreementDetailsSyncAction();

        $array = $this->createMock(ArrayAccess::class);
        $array
            ->expects($this->atLeast(2))
            ->method('offsetExists')
            ->withConsecutive(['agreementRef'], ['orderId'])
            ->willReturn(true)
        ;

        $this->assertFalse($action->supports(new Sync($array)));
    }

    public function testShouldNotSupportAnythingNotSync(): void
    {
        $action = new AgreementDetailsSyncAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportSyncWithNotArrayAccessModel(): void
    {
        $action = new AgreementDetailsSyncAction();

        $this->assertFalse($action->supports(new Sync(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new AgreementDetailsSyncAction();

        $action->execute(new stdClass());
    }

    public function testShouldDoSubExecuteCheckAgreementApiRequest(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CheckAgreement::class))
        ;

        $action = new AgreementDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Sync([
            'agreementRef' => 'aRef',
        ]));
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
