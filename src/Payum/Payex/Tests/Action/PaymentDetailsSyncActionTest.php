<?php
namespace Payum\Payex\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Payex\Action\PaymentDetailsSyncAction;

class PaymentDetailsSyncActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(PaymentDetailsSyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncWithArrayAccessAsModelIfTransactionNumberSet()
    {
        $action = new PaymentDetailsSyncAction();

        $array = $this->createMock('ArrayAccess');
        $array
            ->expects($this->at(0))
            ->method('offsetExists')
            ->with('transactionNumber')
            ->willReturn(true)
        ;

        $this->assertTrue($action->supports(new Sync($array)));
    }

    public function testShouldNotSupportAnythingNotSync()
    {
        $action = new PaymentDetailsSyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportSyncWithNotArrayAccessModel()
    {
        $action = new PaymentDetailsSyncAction();

        $this->assertFalse($action->supports(new Sync(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new PaymentDetailsSyncAction();

        $action->execute(new \stdClass());
    }

    public function testShouldDoSubExecuteCheckOrderApiRequest()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\CheckOrder'))
        ;

        $action = new PaymentDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Sync(array(
            'transactionNumber' => 'aNum',
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
