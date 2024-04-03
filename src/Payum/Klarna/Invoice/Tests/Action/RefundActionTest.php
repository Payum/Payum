<?php

namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Refund;
use Payum\Klarna\Invoice\Action\RefundAction;
use Payum\Klarna\Invoice\Request\Api\CreditPart;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class RefundActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(RefundAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportRefundWithArrayAsModel(): void
    {
        $action = new RefundAction();

        $this->assertTrue($action->supports(new Refund([])));
    }

    public function testShouldNotSupportAnythingNotRefund(): void
    {
        $action = new RefundAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportRefundWithNotArrayAccessModel(): void
    {
        $action = new RefundAction();

        $this->assertFalse($action->supports(new Refund(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new RefundAction();

        $action->execute(new stdClass());
    }

    public function testShouldSubExecuteCreditPartIfNotRefundedYet(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreditPart::class))
        ;

        $action = new RefundAction();
        $action->setGateway($gatewayMock);

        $request = new Refund([
            'invoice_number' => 'aNum',
        ]);

        $action->execute($request);
    }

    public function testShouldDoNothingIfAlreadyRefunded(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new RefundAction();
        $action->setGateway($gatewayMock);

        $request = new Refund([
            'invoice_number' => 'aNum',
            'refund_invoice_number' => 'aFooNum',
        ]);

        $action->execute($request);
    }

    public function testShouldThrowsIfDetailsNotHaveInvoiceNumber(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The invoice_number fields are required.');
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new RefundAction();
        $action->setGateway($gatewayMock);

        $request = new Refund([]);

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
