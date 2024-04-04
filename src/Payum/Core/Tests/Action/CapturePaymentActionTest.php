<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\CapturePaymentAction;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;

class CapturePaymentActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\Capture';

    protected $actionClass = 'Payum\Core\Action\CapturePaymentAction';

    public function provideSupportedRequests(): \Iterator
    {
        $capture = new $this->requestClass($this->createMock(TokenInterface::class));
        $capture->setModel($this->createMock(PaymentInterface::class));
        yield array(new $this->requestClass(new Payment()));
        yield array($capture);
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayAwareInterface'));
    }

    public function testShouldExecuteConvertRequestIfStatusNew()
    {
        $payment = new Payment();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->willReturnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Convert'))
            ->willReturnCallback(function (Convert $request) use ($testCase, $payment) {
                $testCase->assertSame($payment, $request->getSource());
                $testCase->assertSame('array', $request->getTo());
                $testCase->assertNull($request->getToken());

                $request->setResult(array());
            })
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($capture = new Capture($payment));

        $this->assertSame($payment, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());
        $this->assertNull($capture->getToken());
    }

    public function testShouldSetConvertedResultToPaymentAsDetails()
    {
        $payment = new Payment();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->willReturnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Convert'))
            ->willReturnCallback(function (Convert $request) use ($testCase, $payment) {
                $details['foo'] = 'fooVal';

                $request->setResult(array(
                    'foo' => 'fooVal',
                ));
            })
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($capture = new Capture($payment));

        $this->assertSame($payment, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());

        $details = $payment->getDetails();
        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }

    public function testShouldExecuteConvertRequestWithTokenIfOnePresent()
    {
        $payment = new Payment();
        $token = $this->createTokenMock();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->willReturnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Convert'))
            ->willReturnCallback(function (Convert $request) use ($testCase, $payment, $token) {
                $testCase->assertSame($payment, $request->getSource());
                $testCase->assertSame($token, $request->getToken());

                $request->setResult(array());
            })
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $capture = new Capture($token);
        $capture->setModel($payment);

        $action->execute($capture);

        $this->assertSame($payment, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());
        $this->assertSame($token, $capture->getToken());
    }

    public function testShouldSetDetailsBackToPaymentAfterCaptureDetailsExecution()
    {
        $expectedDetails = array('foo' => 'fooVal');

        $payment = new Payment();
        $payment->setDetails($expectedDetails);

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->willReturnCallback(function (GetHumanStatus $request) {
                $request->markPending();
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Capture'))
            ->willReturnCallback(function (Capture $request) use ($testCase, $expectedDetails) {
                $details = $request->getModel();

                $testCase->assertInstanceOf('ArrayAccess', $details);
                $testCase->assertSame($expectedDetails, iterator_to_array($details));

                $details['bar'] = 'barVal';
            })
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($capture = new Capture($payment));

        $this->assertSame($payment, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());
        $this->assertSame(array('foo' => 'fooVal', 'bar' => 'barVal'), $payment->getDetails());
    }

    public function testShouldSetDetailsBackToPaymentEvenIfExceptionThrown()
    {
        $expectedDetails = array('foo' => 'fooVal');

        $payment = new Payment();
        $payment->setDetails($expectedDetails);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHumanStatus'))
            ->willReturnCallback(function (GetHumanStatus $request) {
                $request->markPending();
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Capture'))
            ->willReturnCallback(function (Capture $request) {
                $details = $request->getModel();
                $details['bar'] = 'barVal';

                throw new \Exception();
            })
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $this->expectException('Exception');
        $action->execute($capture = new Capture($payment));

        $this->assertSame($payment, $capture->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $capture->getModel());
        $this->assertSame(array('foo' => 'fooVal', 'bar' => 'barVal'), $payment->getDetails());
    }
}
