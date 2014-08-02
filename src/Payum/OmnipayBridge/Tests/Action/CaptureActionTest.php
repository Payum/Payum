<?php
namespace Payum\OmnipayBridge\Action;

use Omnipay\Common\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\OmnipayBridge\Action\CaptureAction;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\Action\CaptureAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\OmnipayBridge\Action\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureAction;
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

        $action = new CaptureAction;
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
