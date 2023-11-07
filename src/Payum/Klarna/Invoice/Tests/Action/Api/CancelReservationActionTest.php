<?php

namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Klarna;
use KlarnaException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Invoice\Action\Api\CancelReservationAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CancelReservation;
use PHPUnit\Framework\MockObject\MockObject;
use PhpXmlRpc\Client;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class CancelReservationActionTest extends GenericApiAwareActionTest
{
    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new ReflectionClass(CancelReservationAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new CancelReservationAction($this->createKlarnaMock());

        $action->setApi(new stdClass());
    }

    public function testShouldSupportCancelReservationWithArrayAsModel()
    {
        $action = new CancelReservationAction();

        $this->assertTrue($action->supports(new CancelReservation([])));
    }

    public function testShouldNotSupportAnythingNotCancelReservation()
    {
        $action = new CancelReservationAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCancelReservationWithNotArrayAccessModel()
    {
        $action = new CancelReservationAction();

        $this->assertFalse($action->supports(new CancelReservation(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CancelReservationAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfRnoNotSet()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The rno fields are required.');
        $action = new CancelReservationAction();

        $action->execute(new CancelReservation([]));
    }

    public function testShouldCallKlarnaCancelReservationMethod()
    {
        $details = [
            'rno' => 'theRno',
        ];

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('cancelReservation')
            ->with($details['rno'])
            ->willReturn(true)
        ;

        $action = new CancelReservationAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($activate = new CancelReservation($details));

        $canceledDetails = $activate->getModel();

        $this->assertTrue($canceledDetails['canceled']);
    }

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = [
            'rno' => 'theRno',
        ];

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('cancelReservation')
            ->with($details['rno'])
            ->willThrowException(new KlarnaException('theMessage', 123))
        ;

        $action = new CancelReservationAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($cancel = new CancelReservation($details));

        $activatedDetails = $cancel->getModel();
        $this->assertSame(123, $activatedDetails['error_code']);
        $this->assertSame('theMessage', $activatedDetails['error_message']);
    }

    protected function getActionClass(): string
    {
        return CancelReservationAction::class;
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
}
