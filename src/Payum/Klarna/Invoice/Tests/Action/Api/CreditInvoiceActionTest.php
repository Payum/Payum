<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\CreditInvoiceAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CreditInvoice;
use PHPUnit\Framework\TestCase;
use PhpXmlRpc\Client;

class CreditInvoiceActionTest extends GenericApiAwareActionTest
{
    protected function getActionClass(): string
    {
        return CreditInvoiceAction::class;
    }

    protected function getApiClass()
    {
        return new Config();
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CreditInvoiceAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new CreditInvoiceAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportCreditInvoiceWithArrayAsModel()
    {
        $action = new CreditInvoiceAction();

        $this->assertTrue($action->supports(new CreditInvoice(array())));
    }

    public function testShouldNotSupportAnythingNotCreditInvoice()
    {
        $action = new CreditInvoiceAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportCreditInvoiceWithNotArrayAccessModel()
    {
        $action = new CreditInvoiceAction();

        $this->assertFalse($action->supports(new CreditInvoice(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreditInvoiceAction();

        $action->execute(new \stdClass());
    }

    public function testShouldCallKlarnaCreditInvoice()
    {
        $details = array(
            'invoice_number' => 'invoice number',
        );

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

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = array(
            'invoice_number' => 'invoice number',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('creditInvoice')
            ->with(
                $details['invoice_number']
            )
            ->willThrowException(new \KlarnaException('theMessage', 123))
        ;

        $action = new CreditInvoiceAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($reserve = new CreditInvoice($details));

        $postDetails = $reserve->getModel();
        $this->assertSame(123, $postDetails['error_code']);
        $this->assertSame('theMessage', $postDetails['error_message']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->createMock('Klarna', array('config', 'creditInvoice'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
