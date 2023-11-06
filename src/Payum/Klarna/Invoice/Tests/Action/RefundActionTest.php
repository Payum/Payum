<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Refund;
use Payum\Klarna\Invoice\Action\RefundAction;
use PHPUnit\Framework\TestCase;

class RefundActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(RefundAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportRefundWithArrayAsModel()
    {
        $action = new RefundAction();

        $this->assertTrue($action->supports(new Refund(array())));
    }

    public function testShouldNotSupportAnythingNotRefund()
    {
        $action = new RefundAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportRefundWithNotArrayAccessModel()
    {
        $action = new RefundAction();

        $this->assertFalse($action->supports(new Refund(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new RefundAction();

        $action->execute(new \stdClass());
    }

    public function testShouldSubExecuteCreditPartIfNotRefundedYet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\CreditPart'))
        ;

        $action = new RefundAction();
        $action->setGateway($gatewayMock);

        $request = new Refund(array(
            'invoice_number' => 'aNum',
        ));

        $action->execute($request);
    }

    public function testShouldDoNothingIfAlreadyRefunded()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new RefundAction();
        $action->setGateway($gatewayMock);

        $request = new Refund(array(
            'invoice_number' => 'aNum',
            'refund_invoice_number' => 'aFooNum',
        ));

        $action->execute($request);
    }

    public function testShouldThrowsIfDetailsNotHaveInvoiceNumber()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The invoice_number fields are required.');
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new RefundAction();
        $action->setGateway($gatewayMock);

        $request = new Refund(array());

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
