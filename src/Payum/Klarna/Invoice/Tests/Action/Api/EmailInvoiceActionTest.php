<?php

namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Klarna;
use KlarnaException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Invoice\Action\Api\EmailInvoiceAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\EmailInvoice;
use PHPUnit\Framework\MockObject\MockObject;
use PhpXmlRpc\Client;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class EmailInvoiceActionTest extends GenericApiAwareActionTest
{
    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new ReflectionClass(EmailInvoiceAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new EmailInvoiceAction($this->createKlarnaMock());

        $action->setApi(new stdClass());
    }

    public function testShouldSupportEmailInvoiceWithArrayAsModel()
    {
        $action = new EmailInvoiceAction();

        $this->assertTrue($action->supports(new EmailInvoice([])));
    }

    public function testShouldNotSupportAnythingNotEmailInvoice()
    {
        $action = new EmailInvoiceAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportEmailInvoiceWithNotArrayAccessModel()
    {
        $action = new EmailInvoiceAction();

        $this->assertFalse($action->supports(new EmailInvoice(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new EmailInvoiceAction();

        $action->execute(new stdClass());
    }

    public function testShouldCallKlarnaEmailInvoice()
    {
        $details = [
            'invoice_number' => 'invoice number',
        ];

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('emailInvoice')
            ->with(
                $details['invoice_number']
            )
        ;

        $action = new EmailInvoiceAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute(new EmailInvoice($details));
    }

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = [
            'invoice_number' => 'invoice number',
        ];

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('emailInvoice')
            ->with(
                $details['invoice_number']
            )
            ->willThrowException(new KlarnaException('theMessage', 123))
        ;

        $action = new EmailInvoiceAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($reserve = new EmailInvoice($details));

        $postDetails = $reserve->getModel();
        $this->assertSame(123, $postDetails['error_code']);
        $this->assertSame('theMessage', $postDetails['error_message']);
    }

    protected function getActionClass(): string
    {
        return EmailInvoiceAction::class;
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
        $klarnaMock = $this->createMock(Klarna::class);

        $rp = new ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
