<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\CreditPartAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CreditPart;
use PHPUnit\Framework\TestCase;
use PhpXmlRpc\Client;

class CreditPartActionTest extends GenericApiAwareActionTest
{
    protected function getActionClass(): string
    {
        return CreditPartAction::class;
    }

    protected function getApiClass()
    {
        return new Config();
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CreditPartAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    public function testShouldImplementsGatewayAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CreditPartAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayAwareInterface'));
    }

    public function testShouldAllowSetGateway()
    {
        $this->assertInstanceOf(GatewayAwareInterface::class, new CreditPartAction($this->createKlarnaMock()));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new CreditPartAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportCreditPartWithArrayAsModel()
    {
        $action = new CreditPartAction();

        $this->assertTrue($action->supports(new CreditPart(array())));
    }

    public function testShouldNotSupportAnythingNotCreditPart()
    {
        $action = new CreditPartAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportCreditPartWithNotArrayAccessModel()
    {
        $action = new CreditPartAction();

        $this->assertFalse($action->supports(new CreditPart(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreditPartAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfDetailsDoNotHaveInvoiceNumber()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The invoice_number fields are required.');
        $action = new CreditPartAction();

        $action->execute(new CreditPart(array()));
    }

    public function testShouldCallKlarnaCreditPart()
    {
        $details = array(
            'invoice_number' => 'theInvNum',
        );

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails'))
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

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = array(
            'invoice_number' => 'theInvNum',
        );

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails'))
        ;

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('creditPart')
            ->willThrowException(new \KlarnaException('theMessage', 123))
        ;

        $action = new CreditPartAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($creditPart = new CreditPart($details));

        $actualDetails = $creditPart->getModel();
        $this->assertSame(123, $actualDetails['error_code']);
        $this->assertSame('theMessage', $actualDetails['error_message']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->createMock('Klarna', array('config', 'activate', 'cancelReservation', 'checkOrderStatus', 'reserveAmount', 'creditPart'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
