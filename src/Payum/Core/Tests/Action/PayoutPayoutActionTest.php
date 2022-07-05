<?php

namespace Payum\Core\Tests\Action;

use Exception;
use function iterator_to_array;
use Payum\Core\Action\PayoutPayoutAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Payout as PayoutModel;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Payout;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;

class PayoutPayoutActionTest extends GenericActionTest
{
    protected $requestClass = Payout::class;

    protected $actionClass = PayoutPayoutAction::class;

    public function provideSupportedRequests(): \Iterator
    {
        $payout = new $this->requestClass($this->createMock(TokenInterface::class));
        $payout->setModel($this->createMock(PayoutInterface::class));
        yield [new $this->requestClass(new PayoutModel())];
        yield [$payout];
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldExecuteConvertRequestIfStatusNew()
    {
        $payoutModel = new PayoutModel();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive([$this->isInstanceOf(GetHumanStatus::class)], [$this->isInstanceOf(Convert::class)])
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHumanStatus $request) {
                    $request->markNew();
                }),
                $this->returnCallback(function (Convert $request) use ($testCase, $payoutModel) {
                    $testCase->assertSame($payoutModel, $request->getSource());
                    $testCase->assertSame('array', $request->getTo());
                    $testCase->assertNull($request->getToken());

                    $request->setResult([]);
                })
            )
        ;

        $action = new PayoutPayoutAction();
        $action->setGateway($gatewayMock);

        $action->execute($payout = new Payout($payoutModel));

        $this->assertSame($payoutModel, $payout->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $payout->getModel());
        $this->assertNull($payout->getToken());
    }

    public function testShouldSetConvertedResultToPayoutAsDetails()
    {
        $payoutModel = new PayoutModel();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHumanStatus::class)],
                [$this->isInstanceOf(Convert::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHumanStatus $request) {
                    $request->markNew();
                }),
                $this->returnCallback(function (Convert $request) {
                    $request->setResult([
                        'foo' => 'fooVal',
                    ]);
                })
            )
        ;

        $action = new PayoutPayoutAction();
        $action->setGateway($gatewayMock);

        $action->execute($payout = new Payout($payoutModel));

        $this->assertSame($payoutModel, $payout->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $payout->getModel());

        $details = $payoutModel->getDetails();
        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }

    public function testShouldExecuteConvertRequestWithTokenIfOnePresent()
    {
        $payoutModel = new PayoutModel();
        $token = $this->createTokenMock();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive([$this->isInstanceOf(GetHumanStatus::class)], [$this->isInstanceOf(Convert::class)])
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHumanStatus $request) {
                    $request->markNew();
                }),
                $this->returnCallback(function (Convert $request) use ($testCase, $payoutModel, $token) {
                    $testCase->assertSame($payoutModel, $request->getSource());
                    $testCase->assertSame($token, $request->getToken());

                    $request->setResult([]);
                })
            )
        ;

        $action = new PayoutPayoutAction();
        $action->setGateway($gatewayMock);

        $payout = new Payout($token);
        $payout->setModel($payoutModel);

        $action->execute($payout);

        $this->assertSame($payoutModel, $payout->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $payout->getModel());
        $this->assertSame($token, $payout->getToken());
    }

    public function testShouldSetDetailsBackToPayoutAfterPayoutDetailsExecution()
    {
        $expectedDetails = [
            'foo' => 'fooVal',
        ];

        $payoutModel = new PayoutModel();
        $payoutModel->setDetails($expectedDetails);

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive([$this->isInstanceOf(GetHumanStatus::class)], [$this->isInstanceOf(Payout::class)])
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHumanStatus $request) {
                    $request->markPending();
                }),
                $this->returnCallback(function (Payout $request) use ($testCase, $expectedDetails) {
                    $details = $request->getModel();

                    $testCase->assertInstanceOf('ArrayAccess', $details);
                    $testCase->assertSame($expectedDetails, iterator_to_array($details));

                    $details['bar'] = 'barVal';
                })
            )
        ;

        $action = new PayoutPayoutAction();
        $action->setGateway($gatewayMock);

        $action->execute($payout = new Payout($payoutModel));

        $this->assertSame($payoutModel, $payout->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $payout->getModel());
        $this->assertSame([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ], $payoutModel->getDetails());
    }

    public function testShouldSetDetailsBackToPayoutEvenIfExceptionThrown()
    {
        $expectedDetails = [
            'foo' => 'fooVal',
        ];

        $payoutModel = new PayoutModel();
        $payoutModel->setDetails($expectedDetails);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive([$this->isInstanceOf(GetHumanStatus::class)], [$this->isInstanceOf(Payout::class)])
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHumanStatus $request) {
                    $request->markPending();
                }),
                $this->throwException(new Exception())
            )
        ;

        $action = new PayoutPayoutAction();
        $action->setGateway($gatewayMock);

        $this->expectException('Exception');
        $action->execute($payout = new Payout($payoutModel));

        $this->assertSame($payoutModel, $payout->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $payout->getModel());
        $this->assertSame([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ], $payoutModel->getDetails());
    }
}
