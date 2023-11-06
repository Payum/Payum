<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Klarna\Invoice\Action\SyncAction;
use PHPUnit\Framework\TestCase;

class SyncActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(SyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncWithArrayAsModel()
    {
        $action = new SyncAction();

        $this->assertTrue($action->supports(new Sync(array())));
    }

    public function testShouldNotSupportAnythingNotSync()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportSyncWithNotArrayAccessModel()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new Sync(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new SyncAction();

        $action->execute(new \stdClass());
    }

    public function testShouldSubExecuteCheckOrderStatusIfReservedButNotActivated()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\CheckOrderStatus'))
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync(array(
            'rno' => 'aRno',
        ));

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

        $request = new Sync(array());

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

        $request = new Sync(array(
            'rno' => 'aRno',
            'invoice_number' => 'aInvNumber',
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
