<?php

namespace Payum\Payex\Tests\Action;

use ArrayAccess;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Payex\Action\PaymentDetailsSyncAction;
use Payum\Payex\Request\Api\CheckOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class PaymentDetailsSyncActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(PaymentDetailsSyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncWithArrayAccessAsModelIfTransactionNumberSet(): void
    {
        $action = new PaymentDetailsSyncAction();

        $array = $this->createMock(ArrayAccess::class);
        $array
            ->expects($this->once())
            ->method('offsetExists')
            ->with('transactionNumber')
            ->willReturn(true)
        ;

        $this->assertTrue($action->supports(new Sync($array)));
    }

    public function testShouldNotSupportAnythingNotSync(): void
    {
        $action = new PaymentDetailsSyncAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportSyncWithNotArrayAccessModel(): void
    {
        $action = new PaymentDetailsSyncAction();

        $this->assertFalse($action->supports(new Sync(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new PaymentDetailsSyncAction();

        $action->execute(new stdClass());
    }

    public function testShouldDoSubExecuteCheckOrderApiRequest(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CheckOrder::class))
        ;

        $action = new PaymentDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Sync([
            'transactionNumber' => 'aNum',
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
