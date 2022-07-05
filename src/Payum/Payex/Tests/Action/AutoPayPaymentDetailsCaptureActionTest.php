<?php

namespace Payum\Payex\Tests\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Payex\Action\AutoPayPaymentDetailsCaptureAction;
use Payum\Payex\Request\Api\AutoPayAgreement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AutoPayPaymentDetailsCaptureActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new ReflectionClass(AutoPayPaymentDetailsCaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportCaptureWithArrayAsModelIfAutoPaySetToTrue()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new Capture([
            'autoPay' => true,
        ])));
    }

    public function testShouldNotSupportCaptureWithArrayAsModelIfAutoPayNotSet()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture([])));
    }

    public function testShouldNotSupportCaptureWithArrayAsModelIfAutoPaySetToTrueAndRecurringSetToTrue()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture([
            'autoPay' => true,
            'recurring' => true,
        ])));
    }

    public function testShouldNotSupportCaptureayAsModelIfAutoPaySetToFalse()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture([
            'autoPay' => false,
        ])));
    }

    public function testShouldNotSupportAnythingNotCapture()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCaptureWithNotArrayAccessModel()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new AutoPayPaymentDetailsCaptureAction();

        $action->execute(new stdClass());
    }

    public function testShouldDoSubExecuteAutoPayAgreementApiRequest()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(AutoPayAgreement::class))
        ;

        $action = new AutoPayPaymentDetailsCaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture([
            'autoPay' => true,
        ]);

        $action->execute($request);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
