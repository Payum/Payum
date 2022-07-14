<?php

namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Klarna;
use KlarnaException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Invoice\Action\Api\CreditInvoiceAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CreditInvoice;
use PHPUnit\Framework\MockObject\MockObject;
use PhpXmlRpc\Client;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class CreditInvoiceActionTest extends GenericApiAwareActionTest
{
    public function testShouldBeSubClassOfBaseApiAwareAction(): void
    {
        $rc = new ReflectionClass(CreditInvoiceAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new CreditInvoiceAction($this->createKlarnaMock());

        $action->setApi(new stdClass());
    }

    public function testShouldSupportCreditInvoiceWithArrayAsModel(): void
    {
        $action = new CreditInvoiceAction();

        $this->assertTrue($action->supports(new CreditInvoice([])));
    }

    public function testShouldNotSupportAnythingNotCreditInvoice(): void
    {
        $action = new CreditInvoiceAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCreditInvoiceWithNotArrayAccessModel(): void
    {
        $action = new CreditInvoiceAction();

        $this->assertFalse($action->supports(new CreditInvoice(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CreditInvoiceAction();

        $action->execute(new stdClass());
    }

    public function testShouldCallKlarnaCreditInvoice(): void
    {
        $details = [
            'invoice_number' => 'invoice number',
        ];

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('creditInvoice')
            ->with(
                $details['invoice_number']
            )
        ;

        $action = new CreditInvoiceAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute(new CreditInvoice($details));
    }

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails(): void
    {
        $details = [
            'invoice_number' => 'invoice number',
        ];

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('creditInvoice')
            ->with(
                $details['invoice_number']
            )
            ->willThrowException(new KlarnaException('theMessage', 123))
        ;

        $action = new CreditInvoiceAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($reserve = new CreditInvoice($details));

        $postDetails = $reserve->getModel();
        $this->assertSame(123, $postDetails['error_code']);
        $this->assertSame('theMessage', $postDetails['error_message']);
    }

    protected function getActionClass(): string
    {
        return CreditInvoiceAction::class;
    }

    protected function getApiClass()
    {
        return new Config();
    }

    /**
     * @return MockObject|Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock = $this->createMock(Klarna::class, ['config', 'creditInvoice']);

        $rp = new ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
