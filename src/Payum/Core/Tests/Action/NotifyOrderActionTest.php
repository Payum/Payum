<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\NotifyOrderAction;
use Payum\Core\Model\Order;
use Payum\Core\Request\Notify;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;

class NotifyOrderActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\Notify';

    protected $actionClass = 'Payum\Core\Action\NotifyOrderAction';

    public function provideSupportedRequests()
    {
        $capture = new $this->requestClass($this->getMock('Payum\Security\TokenInterface'));
        $capture->setModel($this->getMock('Payum\Core\Model\OrderInterface'));

        return array(
            array(new $this->requestClass(new Order)),
            array($capture),
        );
    }

    /**
     * @test
     */
    public function shouldImplementPaymentAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Action\NotifyOrderAction');

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

        $action = new NotifyOrderAction;
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

        $action = new NotifyOrderAction;
        $action->setPayment($paymentMock);

        $this->setExpectedException('Exception');
        $action->execute($capture = new Notify($order));

        $this->assertSame($order, $capture->getModel());
    }
}