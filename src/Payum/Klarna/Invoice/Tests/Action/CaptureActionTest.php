<?php

namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Klarna\Invoice\Action\CaptureAction;
use Payum\Klarna\Invoice\Request\Api\Activate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CaptureActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportCaptureWithArrayAsModel()
    {
        $action = new CaptureAction();

        $this->assertTrue($action->supports(new Capture([])));
    }

    public function testShouldNotSupportAnythingNotCapture()
    {
        $action = new CaptureAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCaptureWithNotArrayAccessModel()
    {
        $action = new CaptureAction();

        $this->assertFalse($action->supports(new Capture(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CaptureAction();

        $action->execute(new stdClass());
    }

    public function testShouldSubExecuteAuthorizeIfRnoNotSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Authorize::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture([]);

        $action->execute($request);
    }

    public function testShouldSubExecuteActivateIfRnoSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Activate::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture([
            'rno' => 'aRno',
        ]);

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

        $request = new Capture([
            'rno' => 'aRno',
            'invoice_number' => 'anInvNumber',
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
