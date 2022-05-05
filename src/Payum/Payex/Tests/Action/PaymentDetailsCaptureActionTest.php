<?php
namespace Payum\Payex\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Payex\Action\PaymentDetailsCaptureAction;
use Payum\Payex\Request\Api\StartRecurringPayment;
use Payum\Payex\Request\Api\CompleteOrder;
use Payum\Payex\Request\Api\InitializeOrder;

class PaymentDetailsCaptureActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(PaymentDetailsCaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportCaptureWithArrayAsModelIfAutoPayNotSet()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new Capture(array())));
    }

    public function testShouldNotSupportCaptureayAsModelIfAutoPaySet()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(array(
            'autoPay' => true,
        ))));
    }

    public function testShouldSupportCaptureCaptureelIfAutoPaySetToFalse()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new Capture(array(
            'autoPay' => false,
        ))));
    }

    public function testShouldSupportCaptureWithArrCaptureurringSetToTrueAndAutoPaySet()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new Capture(array(
            'autoPay' => true,
            'recurring' => true,
        ))));
    }

    public function testShouldNotSupportAnythingNotCapture()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportCaptureWithNotArrayAccessModel()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new PaymentDetailsCaptureAction();

        $action->execute(new \stdClass());
    }

    public function testShouldDoSubExecuteInitializeOrderApiRequestIfOrderRefNotSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(InitializeOrder::class))
        ;

        $action = new PaymentDetailsCaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'clientIPAddress' => 'anIp',
        ));

        $action->execute($request);
    }

    public function testShouldDoSubExecuteCompleteOrderApiRequestIfOrderRefSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CompleteOrder::class))
        ;

        $action = new PaymentDetailsCaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'orderRef' => 'aRef',
            'clientIPAddress' => 'anIp',
        ));

        $action->execute($request);
    }

    public function testShouldDoSubExecuteStartRecurringPaymentApiRequestIfRecurringSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeastOnce())
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(CompleteOrder::class)],
                [$this->isInstanceOf(StartRecurringPayment::class)]
            )
        ;

        $action = new PaymentDetailsCaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'orderRef' => 'aRef',
            'recurring' => true,
            'clientIPAddress' => 'anIp',
        ));

        $action->execute($request);
    }

    public function testShouldDoSubGetHttpRequestAndSetClientIpFromIt()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeastOnce())
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(InitializeOrder::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request) {
                    $request->clientIp = 'expectedClientIp';
                })
            )
        ;

        $action = new PaymentDetailsCaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture(array());

        $action->execute($request);

        $details = iterator_to_array($request->getModel());

        $this->assertArrayHasKey('clientIPAddress', $details);
        $this->assertSame('expectedClientIp', $details['clientIPAddress']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
