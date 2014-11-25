<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\GenericOrderAction;
use Payum\Core\Model\Order;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Refund;
use Payum\Core\Tests\GenericActionTest;

class GenericOrderActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\Notify';

    protected $actionClass = 'Payum\Core\Action\GenericOrderAction';

    public function provideSupportedRequests()
    {
        $capture = new $this->requestClass($this->getMock('Payum\Security\TokenInterface'));
        $capture->setModel($this->getMock('Payum\Core\Model\OrderInterface'));

        return array(
            array(new $this->requestClass(new Order)),
            array(new Authorize(new Order)),
            array(new Capture(new Order)),
            array(new Refund(new Order)),
            array(new Cancel(new Order)),
            array(new Notify(new Order)),
            array(new GetHumanStatus(new Order())),
        );
    }

    /**
     * @test
     */
    public function shouldImplementPaymentAwareInterface()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface('Payum\Core\PaymentAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldExecuteNotifyDetails()
    {
        $details = array('foo' => 'fooVal');

        $order = new Order;
        $order->setDetails($details);

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Notify'))
            ->will($this->returnCallback(function(Notify $request) use ($testCase, $details) {
                $testCase->assertInstanceOf('ArrayAccess', $request->getModel());
                $testCase->assertEquals($details, iterator_to_array($request->getModel()));
            }))
        ;

        $action = new GenericOrderAction;
        $action->setPayment($paymentMock);

        $action->execute($notify = new Notify($order));

        $this->assertSame($order, $notify->getModel());
    }

    /**
     * @test
     */
    public function shouldKeepOrderEvenIfExceptionThrown()
    {
        $details = array('foo' => 'fooVal');

        $order = new Order;
        $order->setDetails($details);

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Notify'))
            ->will($this->throwException(new \Exception))
        ;

        $action = new GenericOrderAction;
        $action->setPayment($paymentMock);

        $this->setExpectedException('Exception');
        $action->execute($capture = new Notify($order));

        $this->assertSame($order, $capture->getModel());
    }
}