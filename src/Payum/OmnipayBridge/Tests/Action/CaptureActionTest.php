<?php
namespace Payum\OmnipayBridge\Tests\Action;

use Omnipay\Common\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\GenericActionTest;
use Payum\OmnipayBridge\Action\CaptureAction;

class CaptureActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\OmnipayBridge\Action\CaptureAction';

    protected $requestClass = 'Payum\Core\Request\Capture';

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
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\Action\CaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\PaymentAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldCallGatewayPurchaseMethodWithExpectedArguments()
    {
        $details = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
            'card' => array('cvv' => 123),
            'clientIp' => '',
        );

        $requestMock = $this->getMock('Omnipay\Common\Message\RequestInterface');
        $requestMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($this->getMock('Omnipay\Common\Message\ResponseInterface')))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('purchase')
            ->with($details)
            ->will($this->returnValue($requestMock))
        ;

        $action = new CaptureAction;
        $action->setApi($gatewayMock);
        $action->setPayment($this->createPaymentMock());

        $action->execute(new Capture($details));
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
        $action->setPayment($this->createPaymentMock());

        $action->execute(new Capture(array(
            '_status' => 'foo',
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\OmnipayBridge\Tests\DummyGateway');
    }
}
