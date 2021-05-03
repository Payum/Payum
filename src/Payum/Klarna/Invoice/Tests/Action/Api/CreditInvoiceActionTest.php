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

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CreditInvoiceAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new CreditInvoiceAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCreditInvoiceWithArrayAsModel()
    {
        $action = new CreditInvoiceAction();

        $this->assertTrue($action->supports(new CreditInvoice(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCreditInvoice()
    {
        $action = new CreditInvoiceAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCreditInvoiceWithNotArrayAccessModel()
    {
        $action = new CreditInvoiceAction();

        $this->assertFalse($action->supports(new CreditInvoice(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreditInvoiceAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallKlarnaCreditInvoice()
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
            ->method('creditInvoice')
            ->with(
                $details['invoice_number']
            )
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new CreditInvoiceAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($reserve = new CreditInvoice($details));

        $postDetails = $reserve->getModel();
        $this->assertEquals(123, $postDetails['error_code']);
        $this->assertEquals('theMessage', $postDetails['error_message']);
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
