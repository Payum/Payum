<?php

namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Klarna;
use KlarnaException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Invoice\Action\Api\ReserveAmountAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;
use Payum\Klarna\Invoice\Request\Api\ReserveAmount;
use PHPUnit\Framework\MockObject\MockObject;
use PhpXmlRpc\Client;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class ReserveAmountActionTest extends GenericApiAwareActionTest
{
    public function testShouldBeSubClassOfBaseApiAwareAction(): void
    {
        $rc = new ReflectionClass(ReserveAmountAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    public function testShouldImplementsGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(ReserveAmountAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldAllowSetGateway(): void
    {
        $this->assertInstanceOf(GatewayAwareInterface::class, new ReserveAmountAction($this->createKlarnaMock()));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new ReserveAmountAction($this->createKlarnaMock());

        $action->setApi(new stdClass());
    }

    public function testShouldSupportReserveAmountWithArrayAsModel(): void
    {
        $action = new ReserveAmountAction();

        $this->assertTrue($action->supports(new ReserveAmount([])));
    }

    public function testShouldNotSupportAnythingNotReserveAmount(): void
    {
        $action = new ReserveAmountAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportReserveAmountWithNotArrayAccessModel(): void
    {
        $action = new ReserveAmountAction();

        $this->assertFalse($action->supports(new ReserveAmount(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new ReserveAmountAction();

        $action->execute(new stdClass());
    }

    public function testShouldCallKlarnaActivate(): void
    {
        $details = [
            'pno' => 'thePno',
            'gender' => 'theGender',
            'amount' => 'theAmount',
            'reservation_flags' => 'theFlags',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(PopulateKlarnaFromDetails::class))
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
            ->willReturn(['theRno', 'theStatus'])
        ;

        $action = new ReserveAmountAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($reserve = new ReserveAmount($details));

        $reserved = $reserve->getModel();
        $this->assertSame('theRno', $reserved['rno']);
        $this->assertSame('theStatus', $reserved['status']);
    }

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails(): void
    {
        $details = [
            'pno' => 'thePno',
            'gender' => 'theGender',
            'amount' => 'theAmount',
            'reservation_flags' => 'theFlags',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(PopulateKlarnaFromDetails::class))
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
            ->willThrowException(new KlarnaException('theMessage', 123))
        ;

        $action = new ReserveAmountAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($reserve = new ReserveAmount($details));

        $reserved = $reserve->getModel();
        $this->assertSame(123, $reserved['error_code']);
        $this->assertSame('theMessage', $reserved['error_message']);
    }

    protected function getActionClass(): string
    {
        return ReserveAmountAction::class;
    }

    protected function getApiClass()
    {
        return new Config();
    }

    /**
     * @return MockObject|Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock = $this->createMock(Klarna::class);

        $rp = new ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
