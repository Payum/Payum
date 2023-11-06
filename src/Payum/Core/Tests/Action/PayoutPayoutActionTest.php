<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\PayoutPayoutAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Payout as PayoutModel;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
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
        yield array(new $this->requestClass(new PayoutModel()));
        yield array($payout);
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
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHumanStatus::class))
            ->willReturnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Convert::class))
            ->willReturnCallback(function (Convert $request) use ($testCase, $payoutModel) {
                $testCase->assertSame($payoutModel, $request->getSource());
                $testCase->assertSame('array', $request->getTo());
                $testCase->assertNull($request->getToken());

                $request->setResult(array());
            })
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

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHumanStatus::class))
            ->willReturnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Convert::class))
            ->willReturnCallback(function (Convert $request) use ($testCase, $payoutModel) {
                $details['foo'] = 'fooVal';

                $request->setResult(array(
                    'foo' => 'fooVal',
                ));
            })
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
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHumanStatus::class))
            ->willReturnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Convert::class))
            ->willReturnCallback(function (Convert $request) use ($testCase, $payoutModel, $token) {
                $testCase->assertSame($payoutModel, $request->getSource());
                $testCase->assertSame($token, $request->getToken());

                $request->setResult(array());
            })
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
        $expectedDetails = array('foo' => 'fooVal');

        $payoutModel = new PayoutModel();
        $payoutModel->setDetails($expectedDetails);

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHumanStatus::class))
            ->willReturnCallback(function (GetHumanStatus $request) {
                $request->markPending();
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Payout::class))
            ->willReturnCallback(function (Payout $request) use ($testCase, $expectedDetails) {
                $details = $request->getModel();

                $testCase->assertInstanceOf('ArrayAccess', $details);
                $testCase->assertSame($expectedDetails, iterator_to_array($details));

                $details['bar'] = 'barVal';
            })
        ;

        $action = new PayoutPayoutAction();
        $action->setGateway($gatewayMock);

        $action->execute($payout = new Payout($payoutModel));

        $this->assertSame($payoutModel, $payout->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $payout->getModel());
        $this->assertSame(array('foo' => 'fooVal', 'bar' => 'barVal'), $payoutModel->getDetails());
    }

    public function testShouldSetDetailsBackToPayoutEvenIfExceptionThrown()
    {
        $expectedDetails = array('foo' => 'fooVal');

        $payoutModel = new PayoutModel();
        $payoutModel->setDetails($expectedDetails);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHumanStatus::class))
            ->willReturnCallback(function (GetHumanStatus $request) {
                $request->markPending();
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Payout::class))
            ->willReturnCallback(function (Payout $request) {
                $details = $request->getModel();
                $details['bar'] = 'barVal';

                throw new \Exception();
            })
        ;

        $action = new PayoutPayoutAction();
        $action->setGateway($gatewayMock);

        $this->expectException('Exception');
        $action->execute($payout = new Payout($payoutModel));

        $this->assertSame($payoutModel, $payout->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $payout->getModel());
        $this->assertSame(array('foo' => 'fooVal', 'bar' => 'barVal'), $payoutModel->getDetails());
    }
}
