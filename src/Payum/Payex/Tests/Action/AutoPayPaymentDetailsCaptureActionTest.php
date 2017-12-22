<?php
namespace Payum\Payex\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Capture;
use Payum\Payex\Action\AutoPayPaymentDetailsCaptureAction;

class AutoPayPaymentDetailsCaptureActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(AutoPayPaymentDetailsCaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AutoPayPaymentDetailsCaptureAction();
    }

    /**
     * @test
     */
    public function shouldSupportCaptureWithArrayAsModelIfAutoPaySetToTrue()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertTrue($action->supports(new Capture(array(
            'autoPay' => true,
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureWithArrayAsModelIfAutoPayNotSet()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureWithArrayAsModelIfAutoPaySetToTrueAndRecurringSetToTrue()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(array(
            'autoPay' => true,
            'recurring' => true,
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureayAsModelIfAutoPaySetToFalse()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(array(
            'autoPay' => false,
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCapture()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureWithNotArrayAccessModel()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $this->assertFalse($action->supports(new Capture(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new AutoPayPaymentDetailsCaptureAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoSubExecuteAutoPayAgreementApiRequest()
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
