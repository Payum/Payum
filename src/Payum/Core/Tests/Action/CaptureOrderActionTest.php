<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Model\Order;
use Payum\Core\Request\Capture;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;

class CaptureOrderActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\Capture';

    protected $actionClass = 'Payum\Core\Action\CaptureOrderAction';

    public function provideSupportedRequests()
    {
        $capture = new $this->requestClass($this->getMock('Payum\Security\TokenInterface'));
        $capture->setModel($this->getMock('Payum\Core\Model\OrderInterface'));

        return array(
            array(new $this->requestClass(new Order())),
            array($capture),
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
    public function shouldExecuteFillOrderDetailsIfStatusNew()
    {
        $order = new Order();

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            }))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\FillOrderDetails'))
            ->will($this->returnCallback(function (FillOrderDetails $request) use ($testCase, $order) {
                $testCase->assertSame($order, $request->getOrder());
                $testCase->assertNull($request->getToken());
            }))
        ;

        $action = new CaptureOrderAction();
        $action->setPayment($paymentMock);

        $action->execute($capture = new Capture($order));

        $this->assertSame($order, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());
        $this->assertNull($capture->getToken());
    }

    /**
     * @test
     */
    public function shouldKeepFilledDetailsInsideOrder()
    {
        $order = new Order();

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            }))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\FillOrderDetails'))
            ->will($this->returnCallback(function (FillOrderDetails $request) use ($testCase, $order) {
                $testCase->assertSame($order, $request->getOrder());

                $details = $order->getDetails();
                $details['foo'] = 'fooVal';

                $order->setDetails($details);
            }))
        ;

        $action = new CaptureOrderAction();
        $action->setPayment($paymentMock);

        $action->execute($capture = new Capture($order));

        $this->assertSame($order, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());

        $details = $order->getDetails();
        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }

    /**
     * @test
     */
    public function shouldExecuteFillOrderDetailsWithTokenIfStatusNew()
    {
        $order = new Order();
        $token = $this->createTokenMock();

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            }))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\FillOrderDetails'))
            ->will($this->returnCallback(function (FillOrderDetails $request) use ($testCase, $order, $token) {
                $testCase->assertSame($order, $request->getOrder());
                $testCase->assertSame($token, $request->getToken());
            }))
        ;

        $action = new CaptureOrderAction();
        $action->setPayment($paymentMock);

        $capture = new Capture($token);
        $capture->setModel($order);

        $action->execute($capture);

        $this->assertSame($order, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());
        $this->assertSame($token, $capture->getToken());
    }

    /**
     * @test
     */
    public function shouldSetDetailsBackToOrderAfterCaptureDetailsExecution()
    {
        $expectedDetails = array('foo' => 'fooVal');

        $order = new Order();
        $order->setDetails($expectedDetails);

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markPending();
            }))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Capture'))
            ->will($this->returnCallback(function (Capture $request) use ($testCase, $expectedDetails) {
                $details = $request->getModel();

                $testCase->assertInstanceOf('ArrayAccess', $details);
                $testCase->assertEquals($expectedDetails, iterator_to_array($details));

                $details['bar'] = 'barVal';
            }))
        ;

        $action = new CaptureOrderAction();
        $action->setPayment($paymentMock);

        $action->execute($capture = new Capture($order));

        $this->assertSame($order, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());
        $this->assertEquals(array('foo' => 'fooVal', 'bar' => 'barVal'), $order->getDetails());
    }

    /**
     * @test
     */
    public function shouldSetDetailsBackToOrderEvenIfExceptionThrown()
    {
        $expectedDetails = array('foo' => 'fooVal');

        $order = new Order();
        $order->setDetails($expectedDetails);

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markPending();
            }))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Capture'))
            ->will($this->returnCallback(function (Capture $request) {
                $details = $request->getModel();
                $details['bar'] = 'barVal';

                throw new \Exception();
            }))
        ;

        $action = new CaptureOrderAction();
        $action->setPayment($paymentMock);

        $this->setExpectedException('Exception');
        $action->execute($capture = new Capture($order));

        $this->assertSame($order, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());
        $this->assertEquals(array('foo' => 'fooVal', 'bar' => 'barVal'), $order->getDetails());
    }
}
