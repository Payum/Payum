<?php
namespace Payum\OmnipayBridge\Action;

use Omnipay\Common\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\OmnipayBridge\Action\OnsiteCaptureAction;

class OnsiteCaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\Action\OnsiteCaptureAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\OmnipayBridge\Action\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OnsiteCaptureAction;
    }

    /**
     * @test
     */
    public function shouldDoNothingIfStatusAlreadySet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('purchase')
        ;
        $gatewayMock
            ->expects($this->never())
            ->method('completePurchase')
        ;

        $action = new OnsiteCaptureAction;
        $action->setApi($gatewayMock);

        $action->execute(new Capture(array(
            '_status' => 'foo',
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Omnipay\Common\GatewayInterface');
    }
}
