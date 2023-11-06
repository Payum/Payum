<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\EmailInvoiceAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\EmailInvoice;
use PhpXmlRpc\Client;

class EmailInvoiceActionTest extends GenericApiAwareActionTest
{
    protected function getActionClass(): string
    {
        return EmailInvoiceAction::class;
    }

    protected function getApiClass()
    {
        return new Config();
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\EmailInvoiceAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new EmailInvoiceAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportEmailInvoiceWithArrayAsModel()
    {
        $action = new EmailInvoiceAction();

        $this->assertTrue($action->supports(new EmailInvoice(array())));
    }

    public function testShouldNotSupportAnythingNotEmailInvoice()
    {
        $action = new EmailInvoiceAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportEmailInvoiceWithNotArrayAccessModel()
    {
        $action = new EmailInvoiceAction();

        $this->assertFalse($action->supports(new EmailInvoice(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new EmailInvoiceAction();

        $action->execute(new \stdClass());
    }

    public function testShouldCallKlarnaEmailInvoice()
    {
        $details = array(
            'invoice_number' => 'invoice number',
        );

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
        $details = array(
            'invoice_number' => 'invoice number',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('emailInvoice')
            ->with(
                $details['invoice_number']
            )
            ->willThrowException(new \KlarnaException('theMessage', 123))
        ;

        $action = new EmailInvoiceAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($reserve = new EmailInvoice($details));

        $postDetails = $reserve->getModel();
        $this->assertSame(123, $postDetails['error_code']);
        $this->assertSame('theMessage', $postDetails['error_message']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->createMock('Klarna', array('config', 'emailInvoice'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
