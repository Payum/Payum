<?php

namespace Payum\Core\Tests\Action;

use ArrayAccess;
use Exception;
use Iterator;
use function iterator_to_array;
use Payum\Core\Action\CapturePaymentAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use ReflectionClass;

class CapturePaymentActionTest extends GenericActionTest
{
    protected $requestClass = Capture::class;

    protected $actionClass = CapturePaymentAction::class;

    public function provideSupportedRequests(): Iterator
    {
        $capture = new $this->requestClass($this->createMock(TokenInterface::class));
        $capture->setModel($this->createMock(PaymentInterface::class));
        yield [new $this->requestClass(new Payment())];
        yield [$capture];
    }

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldExecuteConvertRequestIfStatusNew(): void
    {
        $payment = new Payment();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive([$this->isInstanceOf(GetHumanStatus::class)], [$this->isInstanceOf(Convert::class)])
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHumanStatus $request): void {
                    $request->markNew();
                }),
                $this->returnCallback(function (Convert $request) use ($testCase, $payment): void {
                    $testCase->assertSame($payment, $request->getSource());
                    $testCase->assertSame('array', $request->getTo());
                    $testCase->assertNull($request->getToken());

                    $request->setResult([]);
                })
            )
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($capture = new Capture($payment));

        $this->assertSame($payment, $capture->getFirstModel());
        $this->assertInstanceOf(ArrayAccess::class, $capture->getModel());
        $this->assertNull($capture->getToken());
    }

    public function testShouldSetConvertedResultToPaymentAsDetails(): void
    {
        $payment = new Payment();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive([$this->isInstanceOf(GetHumanStatus::class)], [$this->isInstanceOf(Convert::class)])
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHumanStatus $request): void {
                    $request->markNew();
                }),
                $this->returnCallback(function (Convert $request): void {
                    $request->setResult([
                        'foo' => 'fooVal',
                    ]);
                })
            )
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($capture = new Capture($payment));

        $this->assertSame($payment, $capture->getFirstModel());
        $this->assertInstanceOf(ArrayAccess::class, $capture->getModel());

        $details = $payment->getDetails();
        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }

    public function testShouldExecuteConvertRequestWithTokenIfOnePresent(): void
    {
        $payment = new Payment();
        $token = $this->createTokenMock();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive([$this->isInstanceOf(GetHumanStatus::class)], [$this->isInstanceOf(Convert::class)])
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHumanStatus $request): void {
                    $request->markNew();
                }),
                $this->returnCallback(function (Convert $request) use ($testCase, $payment, $token): void {
                    $testCase->assertSame($payment, $request->getSource());
                    $testCase->assertSame($token, $request->getToken());

                    $request->setResult([]);
                })
            )
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $capture = new Capture($token);
        $capture->setModel($payment);

        $action->execute($capture);

        $this->assertSame($payment, $capture->getFirstModel());
        $this->assertInstanceOf(ArrayAccess::class, $capture->getModel());
        $this->assertSame($token, $capture->getToken());
    }

    public function testShouldSetDetailsBackToPaymentAfterCaptureDetailsExecution(): void
    {
        $expectedDetails = [
            'foo' => 'fooVal',
        ];

        $payment = new Payment();
        $payment->setDetails($expectedDetails);

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive([$this->isInstanceOf(GetHumanStatus::class)], [$this->isInstanceOf(Capture::class)])
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHumanStatus $request): void {
                    $request->markPending();
                }),
                $this->returnCallback(function (Capture $request) use ($testCase, $expectedDetails): void {
                    $details = $request->getModel();

                    $testCase->assertInstanceOf(ArrayAccess::class, $details);
                    $testCase->assertEquals($expectedDetails, iterator_to_array($details));

                    $details['bar'] = 'barVal';
                })
            )
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($capture = new Capture($payment));

        $this->assertSame($payment, $capture->getFirstModel());
        $this->assertInstanceOf(ArrayAccess::class, $capture->getModel());
        $this->assertEquals([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ], $payment->getDetails());
    }

    public function testShouldSetDetailsBackToPaymentEvenIfExceptionThrown(): void
    {
        $expectedDetails = [
            'foo' => 'fooVal',
        ];

        $payment = new Payment();
        $payment->setDetails($expectedDetails);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive([$this->isInstanceOf(GetHumanStatus::class)], [$this->isInstanceOf(Capture::class)])
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHumanStatus $request): void {
                    $request->markPending();
                }),
                $this->returnCallback(function (Capture $request): void {
                    $details = $request->getModel();
                    $details['bar'] = 'barVal';

                    throw new Exception();
                })
            )
        ;

        $action = new CapturePaymentAction();
        $action->setGateway($gatewayMock);

        $this->expectException('Exception');
        $action->execute($capture = new Capture($payment));

        $this->assertSame($payment, $capture->getFirstModel());
        $this->assertInstanceOf(ArrayAccess::class, $capture->getModel());
        $this->assertEquals([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ], $payment->getDetails());
    }
}
