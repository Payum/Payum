<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\SkipOnPhp7Trait;
use Payum\Klarna\Invoice\Action\Api\SendInvoiceAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\SendInvoice;

class SendInvoiceActionTest extends \PHPUnit_Framework_TestCase
{
    use SkipOnPhp7Trait;

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\SendInvoiceAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new SendInvoiceAction();
    }

    /**
     * @test
     */
    public function couldBeConstructedWithKlarnaAsArgument()
    {
        new SendInvoiceAction($this->createKlarnaMock());
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = new SendInvoiceAction($this->createKlarnaMock());

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
        $action = new SendInvoiceAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportSendInvoiceWithArrayAsModel()
    {
        $action = new SendInvoiceAction();

        $this->assertTrue($action->supports(new SendInvoice(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSendInvoice()
    {
        $action = new SendInvoiceAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportSendInvoiceWithNotArrayAccessModel()
    {
        $action = new SendInvoiceAction();

        $this->assertFalse($action->supports(new SendInvoice(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new SendInvoiceAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallKlarnaSendInvoice()
    {
        $details = array(
            'invoice_number' => 'invoice number',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('sendInvoice')
            ->with(
                $details['invoice_number']
            )
        ;

        $action = new SendInvoiceAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute(new SendInvoice($details));
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
            ->method('sendInvoice')
            ->with(
                $details['invoice_number']
            )
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new SendInvoiceAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($reserve = new SendInvoice($details));

        $postDetails = $reserve->getModel();
        $this->assertEquals(123, $postDetails['error_code']);
        $this->assertEquals('theMessage', $postDetails['error_message']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->getMock('Klarna', array('config', 'sendInvoice'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->getMock('xmlrpc_client', array(), array(), '', false));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
