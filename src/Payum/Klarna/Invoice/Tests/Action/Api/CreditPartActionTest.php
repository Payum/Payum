<?php

namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Klarna;
use KlarnaException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Invoice\Action\Api\CreditPartAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CreditPart;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PhpXmlRpc\Client;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class CreditPartActionTest extends GenericApiAwareActionTest
{
    public function testShouldBeSubClassOfBaseApiAwareAction(): void
    {
        $rc = new ReflectionClass(CreditPartAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    public function testShouldImplementsGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(CreditPartAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldAllowSetGateway(): void
    {
        $this->assertInstanceOf(GatewayAwareInterface::class, new CreditPartAction($this->createKlarnaMock()));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new CreditPartAction($this->createKlarnaMock());

        $action->setApi(new stdClass());
    }

    public function testShouldSupportCreditPartWithArrayAsModel(): void
    {
        $action = new CreditPartAction();

        $this->assertTrue($action->supports(new CreditPart([])));
    }

    public function testShouldNotSupportAnythingNotCreditPart(): void
    {
        $action = new CreditPartAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCreditPartWithNotArrayAccessModel(): void
    {
        $action = new CreditPartAction();

        $this->assertFalse($action->supports(new CreditPart(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CreditPartAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfDetailsDoNotHaveInvoiceNumber(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The invoice_number fields are required.');
        $action = new CreditPartAction();

        $action->execute(new CreditPart([]));
    }

    public function testShouldCallKlarnaCreditPart(): void
    {
        $details = [
            'invoice_number' => 'theInvNum',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(PopulateKlarnaFromDetails::class))
        ;

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('creditPart')
            ->with($details['invoice_number'])
            ->willReturn('theRefundInvoiceNumber')
        ;

        $action = new CreditPartAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($creditPart = new CreditPart($details));

        $actualDetails = $creditPart->getModel();
        $this->assertStringContainsString('theRefundInvoiceNumber', $actualDetails['refund_invoice_number']);
    }

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails(): void
    {
        $details = [
            'invoice_number' => 'theInvNum',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(PopulateKlarnaFromDetails::class))
        ;

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('creditPart')
            ->willThrowException(new KlarnaException('theMessage', 123))
        ;

        $action = new CreditPartAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($creditPart = new CreditPart($details));

        $actualDetails = $creditPart->getModel();
        $this->assertSame(123, $actualDetails['error_code']);
        $this->assertSame('theMessage', $actualDetails['error_message']);
    }

    protected function getActionClass(): string
    {
        return CreditPartAction::class;
    }

    protected function getApiClass(): Config
    {
        return new Config();
    }

    /**
     * @return MockObject|Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock = $this->createMock(Klarna::class);

        $rp = new ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
