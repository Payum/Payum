<?php

namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Klarna\Invoice\Action\SyncAction;
use Payum\Klarna\Invoice\Request\Api\CheckOrderStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class SyncActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new ReflectionClass(SyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncWithArrayAsModel()
    {
        $action = new SyncAction();

        $this->assertTrue($action->supports(new Sync([])));
    }

    public function testShouldNotSupportAnythingNotSync()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportSyncWithNotArrayAccessModel()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new Sync(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new SyncAction();

        $action->execute(new stdClass());
    }

    public function testShouldSubExecuteCheckOrderStatusIfReservedButNotActivated()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CheckOrderStatus::class))
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync([
            'rno' => 'aRno',
        ]);

        $action->execute($request);
    }

    public function testShouldNotSubExecuteCheckOrderStatusIfNotReserved()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync([]);

        $action->execute($request);
    }

    public function testShouldNotSubExecuteCheckOrderStatusIfReservedAndActivated()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync([
            'rno' => 'aRno',
            'invoice_number' => 'aInvNumber',
        ]);

        $action->execute($request);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
