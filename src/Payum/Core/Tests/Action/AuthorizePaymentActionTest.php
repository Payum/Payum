<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\AuthorizePaymentAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;

class AuthorizePaymentActionTest extends GenericActionTest
{
    protected $requestClass = Authorize::class;

    protected $actionClass = AuthorizePaymentAction::class;

    public function provideSupportedRequests()
    {
        $authorize = new $this->requestClass($this->getMock(TokenInterface::class));
        $authorize->setModel($this->getMock(PaymentInterface::class));

        return array(
            array(new $this->requestClass(new Payment())),
            array($authorize),
        );
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldExecuteConvertRequestIfStatusNew()
    {
        $payment = new Payment();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHumanStatus::class))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Convert::class))
            ->will($this->returnCallback(function (Convert $request) use ($testCase, $payment) {
                $testCase->assertSame($payment, $request->getSource());
                $testCase->assertSame('array', $request->getTo());
                $testCase->assertNull($request->getToken());

                $request->setResult(array());
            }))
        ;

        $action = new AuthorizePaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($authorize = new Authorize($payment));

        $this->assertSame($payment, $authorize->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $authorize->getModel());
        $this->assertNull($authorize->getToken());
    }

    /**
     * @test
     */
    public function shouldSetConvertedResultToPaymentAsDetails()
    {
        $payment = new Payment();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHumanStatus::class))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Convert::class))
            ->will($this->returnCallback(function (Convert $request) use ($testCase, $payment) {
                $details['foo'] = 'fooVal';

                $request->setResult(array(
                    'foo' => 'fooVal',
                ));
            }))
        ;

        $action = new AuthorizePaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($authorize = new Authorize($payment));

        $this->assertSame($payment, $authorize->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $authorize->getModel());

        $details = $payment->getDetails();
        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }

    /**
     * @test
     */
    public function shouldExecuteConvertRequestWithTokenIfOnePresent()
    {
        $payment = new Payment();
        $token = $this->createTokenMock();

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHumanStatus::class))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markNew();
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Convert::class))
            ->will($this->returnCallback(function (Convert $request) use ($testCase, $payment, $token) {
                $testCase->assertSame($payment, $request->getSource());
                $testCase->assertSame($token, $request->getToken());

                $request->setResult(array());
            }))
        ;

        $action = new AuthorizePaymentAction();
        $action->setGateway($gatewayMock);

        $authorize = new Authorize($token);
        $authorize->setModel($payment);

        $action->execute($authorize);

        $this->assertSame($payment, $authorize->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $authorize->getModel());
        $this->assertSame($token, $authorize->getToken());
    }

    /**
     * @test
     */
    public function shouldSetDetailsBackToPaymentAfterAuthorizeDetailsExecution()
    {
        $expectedDetails = array('foo' => 'fooVal');

        $payment = new Payment();
        $payment->setDetails($expectedDetails);

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHumanStatus::class))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markPending();
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Authorize::class))
            ->will($this->returnCallback(function (Authorize $request) use ($testCase, $expectedDetails) {
                $details = $request->getModel();

                $testCase->assertInstanceOf('ArrayAccess', $details);
                $testCase->assertEquals($expectedDetails, iterator_to_array($details));

                $details['bar'] = 'barVal';
            }))
        ;

        $action = new AuthorizePaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($authorize = new Authorize($payment));

        $this->assertSame($payment, $authorize->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $authorize->getModel());
        $this->assertEquals(array('foo' => 'fooVal', 'bar' => 'barVal'), $payment->getDetails());
    }

    /**
     * @test
     */
    public function shouldSetDetailsBackToPaymentEvenIfExceptionThrown()
    {
        $expectedDetails = array('foo' => 'fooVal');

        $payment = new Payment();
        $payment->setDetails($expectedDetails);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHumanStatus::class))
            ->will($this->returnCallback(function (GetHumanStatus $request) {
                $request->markPending();
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Authorize::class))
            ->will($this->returnCallback(function (Authorize $request) {
                $details = $request->getModel();
                $details['bar'] = 'barVal';

                throw new \Exception();
            }))
        ;

        $action = new AuthorizePaymentAction();
        $action->setGateway($gatewayMock);

        $this->setExpectedException('Exception');
        $action->execute($authorize = new Authorize($payment));

        $this->assertSame($payment, $authorize->getFirstModel());
        $this->assertInstanceOf('ArrayAccess', $authorize->getModel());
        $this->assertEquals(array('foo' => 'fooVal', 'bar' => 'barVal'), $payment->getDetails());
    }
}
