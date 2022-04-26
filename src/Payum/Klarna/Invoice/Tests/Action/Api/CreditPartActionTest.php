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

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CreditPartAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function shouldImplementsGatewayAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CreditPartAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowSetGateway()
    {
        $this->assertInstanceOf(GatewayAwareInterface::class, new CreditPartAction($this->createKlarnaMock()));
    }

    /**
     * @test
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new CreditPartAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCreditPartWithArrayAsModel()
    {
        $action = new CreditPartAction();

        $this->assertTrue($action->supports(new CreditPart(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCreditPart()
    {
        $action = new CreditPartAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCreditPartWithNotArrayAccessModel()
    {
        $action = new CreditPartAction();

        $this->assertFalse($action->supports(new CreditPart(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreditPartAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfDetailsDoNotHaveInvoiceNumber()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The invoice_number fields are required.');
        $action = new CreditPartAction();

        $action->execute(new CreditPart(array()));
    }

    /**
     * @test
     */
    public function shouldCallKlarnaCreditPart()
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
            ->will($this->returnValue('theRefundInvoiceNumber'))
        ;

        $action = new CreditPartAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($creditPart = new CreditPart($details));

        $actualDetails = $creditPart->getModel();
        $this->assertStringContainsString('theRefundInvoiceNumber', $actualDetails['refund_invoice_number']);
    }

    /**
     * @test
     */
    public function shouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
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
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new CreditPartAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($creditPart = new CreditPart($details));

        $actualDetails = $creditPart->getModel();
        $this->assertEquals(123, $actualDetails['error_code']);
        $this->assertEquals('theMessage', $actualDetails['error_message']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Klarna
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
     * @return \PHPUnit\Framework\MockObject\MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
