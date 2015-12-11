<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\SkipOnPhp7Trait;
use Payum\Klarna\Invoice\Action\Api\CreditInvoiceAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CreditInvoice;

class CreditInvoiceActionTest extends \PHPUnit_Framework_TestCase
{
    use SkipOnPhp7Trait;

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
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreditInvoiceAction();
    }

    /**
     * @test
     */
    public function couldBeConstructedWithKlarnaAsArgument()
    {
        new CreditInvoiceAction($this->createKlarnaMock());
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = new CreditInvoiceAction($this->createKlarnaMock());

        $action->setApi($config = new Config());

        $this->assertAttributeSame($config, 'config', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     * @expectedExceptionMessage Instance of Config is expected to be passed as api.
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
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
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
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
        $klarnaMock =  $this->getMock('Klarna', array('config', 'creditInvoice'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->getMock('xmlrpc_client', array(), array(), '', false));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
