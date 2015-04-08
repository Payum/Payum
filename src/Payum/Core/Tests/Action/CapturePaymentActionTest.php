<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\CapturePaymentAction;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Capture;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;

class CapturePaymentActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\Capture';

    protected $actionClass = 'Payum\Core\Action\CapturePaymentAction';

    public function provideSupportedRequests()
    {
        $capture = new $this->requestClass($this->getMock('Payum\Security\TokenInterface'));
        $capture->setModel($this->getMock('Payum\Core\Model\PaymentInterface'));

        return array(
            array(new $this->requestClass(new Payment())),
            array($capture),
        );
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldExecuteFillOrderDetailsIfStatusNew()
    {
        $order = new Payment();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\FillOrderDetails'))
            ->will($this->returnCallback(function (FillOrderDetails $request) use ($testCase, $order) {
                $testCase->assertSame($order, $request->getOrder());
                $testCase->assertNull($request->getToken());
            }))
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

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
        $order = new Payment();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            }))
        ;
        $gatewayMock
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

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

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
        $order = new Payment();
        $token = $this->createTokenMock();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\FillOrderDetails'))
            ->will($this->returnCallback(function (FillOrderDetails $request) use ($testCase, $order, $token) {
                $testCase->assertSame($order, $request->getOrder());
                $testCase->assertSame($token, $request->getToken());
            }))
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

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

        $order = new Payment();
        $order->setDetails($expectedDetails);

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markPending();
            }))
        ;
        $gatewayMock
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

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

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

        $order = new Payment();
        $order->setDetails($expectedDetails);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markPending();
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Capture'))
            ->will($this->returnCallback(function (Capture $request) {
                $details = $request->getModel();
                $details['bar'] = 'barVal';

                throw new \Exception();
            }))
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $this->setExpectedException('Exception');
        $action->execute($capture = new Capture($order));

        $this->assertSame($order, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());
        $this->assertEquals(array('foo' => 'fooVal', 'bar' => 'barVal'), $order->getDetails());
    }
}
