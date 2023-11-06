<?php
namespace Payum\Payex\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Capture;
use Payum\Payex\Action\AutoPayPaymentDetailsCaptureAction;

class AutoPayPaymentDetailsCaptureActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(AutoPayPaymentDetailsCaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportCaptureWithArrayAsModelIfAutoPaySetToTrue()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new Capture(array(
            'autoPay' => true,
        ))));
    }

    public function testShouldNotSupportCaptureWithArrayAsModelIfAutoPayNotSet()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(array())));
    }

    public function testShouldNotSupportCaptureWithArrayAsModelIfAutoPaySetToTrueAndRecurringSetToTrue()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(array(
            'autoPay' => true,
            'recurring' => true,
        ))));
    }

    public function testShouldNotSupportCaptureayAsModelIfAutoPaySetToFalse()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(array(
            'autoPay' => false,
        ))));
    }

    public function testShouldNotSupportAnythingNotCapture()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportCaptureWithNotArrayAccessModel()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new AutoPayPaymentDetailsCaptureAction();

        $action->execute(new \stdClass());
    }

    public function testShouldDoSubExecuteAutoPayAgreementApiRequest()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\AutoPayAgreement'))
        ;

        $action = new AutoPayPaymentDetailsCaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'autoPay' => true,
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
