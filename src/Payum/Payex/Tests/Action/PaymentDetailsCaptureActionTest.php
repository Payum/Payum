<?php
namespace Payum\Payex\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Payex\Action\PaymentDetailsCaptureAction;

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
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\InitializeOrder'))
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
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\CompleteOrder'))
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
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\StartRecurringPayment'))
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
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHttpRequest'))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->clientIp = 'expectedClientIp';
            })
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
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
