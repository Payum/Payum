<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\ReserveAmountAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\ReserveAmount;
use PHPUnit\Framework\TestCase;
use PhpXmlRpc\Client;

class ReserveAmountActionTest extends GenericApiAwareActionTest
{
    protected function getActionClass(): string
    {
        return ReserveAmountAction::class;
    }

    protected function getApiClass()
    {
        return new Config();
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\ReserveAmountAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    public function testShouldImplementsGatewayAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\ReserveAmountAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayAwareInterface'));
    }

    public function testShouldAllowSetGateway()
    {
        $this->assertInstanceOf(GatewayAwareInterface::class, new ReserveAmountAction($this->createKlarnaMock()));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new ReserveAmountAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportReserveAmountWithArrayAsModel()
    {
        $action = new ReserveAmountAction();

        $this->assertTrue($action->supports(new ReserveAmount(array())));
    }

    public function testShouldNotSupportAnythingNotReserveAmount()
    {
        $action = new ReserveAmountAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportReserveAmountWithNotArrayAccessModel()
    {
        $action = new ReserveAmountAction();

        $this->assertFalse($action->supports(new ReserveAmount(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new ReserveAmountAction();

        $action->execute(new \stdClass());
    }

    public function testShouldCallKlarnaActivate()
    {
        $details = array(
            'pno' => 'thePno',
            'gender' => 'theGender',
            'amount' => 'theAmount',
            'reservation_flags' => 'theFlags',
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
            ->method('reserveAmount')
            ->with(
                $details['pno'],
                $details['gender'],
                $details['amount'],
                $details['reservation_flags']
            )
            ->willReturn(array('theRno', 'theStatus'))
        ;

        $action = new ReserveAmountAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($reserve = new ReserveAmount($details));

        $reserved = $reserve->getModel();
        $this->assertSame('theRno', $reserved['rno']);
        $this->assertSame('theStatus', $reserved['status']);
    }

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = array(
            'pno' => 'thePno',
            'gender' => 'theGender',
            'amount' => 'theAmount',
            'reservation_flags' => 'theFlags',
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
            ->method('reserveAmount')
            ->with(
                $details['pno'],
                $details['gender'],
                $details['amount'],
                $details['reservation_flags']
            )
            ->willThrowException(new \KlarnaException('theMessage', 123))
        ;

        $action = new ReserveAmountAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($reserve = new ReserveAmount($details));

        $reserved = $reserve->getModel();
        $this->assertSame(123, $reserved['error_code']);
        $this->assertSame('theMessage', $reserved['error_message']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->createMock('Klarna', array('config', 'activate', 'cancelReservation', 'checkOrderStatus', 'reserveAmount'));

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
