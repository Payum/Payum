<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Refund;
use Payum\Klarna\Invoice\Action\RefundAction;
use PHPUnit\Framework\TestCase;

class RefundActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(RefundAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RefundAction();
    }

    /**
     * @test
     */
    public function shouldSupportRefundWithArrayAsModel()
    {
        $action = new RefundAction();

        $this->assertTrue($action->supports(new Refund(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotRefund()
    {
        $action = new RefundAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportRefundWithNotArrayAccessModel()
    {
        $action = new RefundAction();

        $this->assertFalse($action->supports(new Refund(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new RefundAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSubExecuteCreditPartIfNotRefundedYet()
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

    /**
     * @test
     */
    public function shouldDoNothingIfAlreadyRefunded()
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

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The invoice_number fields are required.
     */
    public function shouldThrowsIfDetailsNotHaveInvoiceNumber()
    {
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
