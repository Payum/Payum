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

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\EmailInvoiceAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new EmailInvoiceAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportEmailInvoiceWithArrayAsModel()
    {
        $action = new EmailInvoiceAction();

        $this->assertTrue($action->supports(new EmailInvoice(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotEmailInvoice()
    {
        $action = new EmailInvoiceAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportEmailInvoiceWithNotArrayAccessModel()
    {
        $action = new EmailInvoiceAction();

        $this->assertFalse($action->supports(new EmailInvoice(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new EmailInvoiceAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallKlarnaEmailInvoice()
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

    /**
     * @test
     */
    public function shouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
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
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new EmailInvoiceAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($reserve = new EmailInvoice($details));

        $postDetails = $reserve->getModel();
        $this->assertEquals(123, $postDetails['error_code']);
        $this->assertEquals('theMessage', $postDetails['error_message']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Klarna
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
