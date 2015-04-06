<?php
namespace Payum\Payex\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Payex\Action\AgreementDetailsSyncAction;

class AgreementDetailsSyncActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\AgreementDetailsSyncAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\GatewayAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AgreementDetailsSyncAction();
    }

    /**
     * @test
     */
    public function shouldSupportSyncWithArrayAccessAsModelIfOrderIdNotSetAndAgreementRefSet()
    {
        $action = new AgreementDetailsSyncAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->at(0))
            ->method('offsetExists')
            ->with('agreementRef')
            ->will($this->returnValue(true))
        ;
        $array
            ->expects($this->at(1))
            ->method('offsetExists')
            ->with('orderId')
            ->will($this->returnValue(false))
        ;

        $this->assertTrue($action->supports(new Sync($array)));
    }

    /**
     * @test
     */
    public function shouldNotSupportSyncWithArrayAccessAsModelIfOrderIdAndAgreementRefSet()
    {
        $action = new AgreementDetailsSyncAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->at(0))
            ->method('offsetExists')
            ->with('agreementRef')
            ->will($this->returnValue(true))
        ;
        $array
            ->expects($this->at(1))
            ->method('offsetExists')
            ->with('orderId')
            ->will($this->returnValue(true))
        ;

        $this->assertFalse($action->supports(new Sync($array)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSync()
    {
        $action = new AgreementDetailsSyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportSyncWithNotArrayAccessModel()
    {
        $action = new AgreementDetailsSyncAction();

        $this->assertFalse($action->supports(new Sync(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new AgreementDetailsSyncAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoSubExecuteCheckAgreementApiRequest()
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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
