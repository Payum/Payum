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
    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(PaymentDetailsCaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureWithArrayAsModelIfAutoPayNotSet()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new Capture(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureayAsModelIfAutoPaySet()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(array(
            'autoPay' => true,
        ))));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureCaptureelIfAutoPaySetToFalse()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new Capture(array(
            'autoPay' => false,
        ))));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureWithArrCaptureurringSetToTrueAndAutoPaySet()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new Capture(array(
            'autoPay' => true,
            'recurring' => true,
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCapture()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureWithNotArrayAccessModel()
    {
        $action = new PaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new PaymentDetailsCaptureAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoSubExecuteInitializeOrderApiRequestIfOrderRefNotSet()
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

    /**
     * @test
     */
    public function shouldDoSubExecuteCompleteOrderApiRequestIfOrderRefSet()
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

    /**
     * @test
     */
    public function shouldDoSubExecuteStartRecurringPaymentApiRequestIfRecurringSet()
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

    /**
     * @test
     */
    public function shouldDoSubGetHttpRequestAndSetClientIpFromIt()
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
        $this->assertEquals('expectedClientIp', $details['clientIPAddress']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
