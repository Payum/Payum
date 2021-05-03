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

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\ReserveAmountAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function shouldImplementsGatewayAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\ReserveAmountAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowSetGateway()
    {
        $this->assertInstanceOf(GatewayAwareInterface::class, new ReserveAmountAction($this->createKlarnaMock()));
    }

    /**
     * @test
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new ReserveAmountAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportReserveAmountWithArrayAsModel()
    {
        $action = new ReserveAmountAction();

        $this->assertTrue($action->supports(new ReserveAmount(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotReserveAmount()
    {
        $action = new ReserveAmountAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportReserveAmountWithNotArrayAccessModel()
    {
        $action = new ReserveAmountAction();

        $this->assertFalse($action->supports(new ReserveAmount(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new ReserveAmountAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallKlarnaActivate()
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
            ->will($this->returnValue(array('theRno', 'theStatus')))
        ;

        $action = new ReserveAmountAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($reserve = new ReserveAmount($details));

        $reserved = $reserve->getModel();
        $this->assertEquals('theRno', $reserved['rno']);
        $this->assertEquals('theStatus', $reserved['status']);
    }

    /**
     * @test
     */
    public function shouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
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
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new ReserveAmountAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($reserve = new ReserveAmount($details));

        $reserved = $reserve->getModel();
        $this->assertEquals(123, $reserved['error_code']);
        $this->assertEquals('theMessage', $reserved['error_message']);
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
