<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Klarna\Invoice\Action\CaptureAction;
use PHPUnit\Framework\TestCase;

class CaptureActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportCaptureWithArrayAsModel()
    {
        $action = new CaptureAction();

        $this->assertTrue($action->supports(new Capture(array())));
    }

    public function testShouldNotSupportAnythingNotCapture()
    {
        $action = new CaptureAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportCaptureWithNotArrayAccessModel()
    {
        $action = new CaptureAction();

        $this->assertFalse($action->supports(new Capture(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CaptureAction();

        $action->execute(new \stdClass());
    }

    public function testShouldSubExecuteAuthorizeIfRnoNotSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Authorize'))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture(array());

        $action->execute($request);
    }

    public function testShouldSubExecuteActivateIfRnoSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\Activate'))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'rno' => 'aRno',
        ));

        $action->execute($request);
    }

    public function testShouldDoNothingIfAlreadyReservedAndActivated()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'rno' => 'aRno',
            'invoice_number' => 'anInvNumber',
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
