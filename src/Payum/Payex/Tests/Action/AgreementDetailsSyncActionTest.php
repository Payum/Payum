<?php
namespace Payum\Payex\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Payex\Action\AgreementDetailsSyncAction;

class AgreementDetailsSyncActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(AgreementDetailsSyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncWithArrayAccessAsModelIfOrderIdNotSetAndAgreementRefSet()
    {
        $action = new AgreementDetailsSyncAction();

        $array = $this->createMock('ArrayAccess');
        $array
            ->expects($this->atLeast(2))
            ->method('offsetExists')
            ->withConsecutive(['agreementRef'], ['orderId'])
            ->willReturnOnConsecutiveCalls(true, false)
        ;

        $this->assertTrue($action->supports(new Sync($array)));
    }

    public function testShouldNotSupportSyncWithArrayAccessAsModelIfOrderIdAndAgreementRefSet()
    {
        $action = new AgreementDetailsSyncAction();

        $array = $this->createMock('ArrayAccess');
        $array
            ->expects($this->atLeast(2))
            ->method('offsetExists')
            ->withConsecutive(['agreementRef'], ['orderId'])
            ->willReturn(true)
        ;

        $this->assertFalse($action->supports(new Sync($array)));
    }

    public function testShouldNotSupportAnythingNotSync()
    {
        $action = new AgreementDetailsSyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportSyncWithNotArrayAccessModel()
    {
        $action = new AgreementDetailsSyncAction();

        $this->assertFalse($action->supports(new Sync(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new AgreementDetailsSyncAction();

        $action->execute(new \stdClass());
    }

    public function testShouldDoSubExecuteCheckAgreementApiRequest()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\CheckAgreement'))
        ;

        $action = new AgreementDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Sync(array(
            'agreementRef' => 'aRef',
        )));
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
