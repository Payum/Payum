<?php

namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Klarna;
use KlarnaException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\ActivateAction;
use Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\Activate;
use PHPUnit\Framework\MockObject\MockObject;
use PhpXmlRpc\Client;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class ActivateActionTest extends GenericApiAwareActionTest
{
    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new ReflectionClass(ActivateAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new ActivateAction($this->createKlarnaMock());

        $action->setApi(new stdClass());
    }

    public function testShouldSupportActivateWithArrayAsModel()
    {
        $action = new ActivateAction();

        $this->assertTrue($action->supports(new Activate([])));
    }

    public function testShouldNotSupportAnythingNotActivate()
    {
        $action = new ActivateAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportActivateWithNotArrayAccessModel()
    {
        $action = new ActivateAction();

        $this->assertFalse($action->supports(new Activate(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new ActivateAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfRnoNotSet()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The rno fields are required.');
        $action = new ActivateAction();

        $action->execute(new Activate([]));
    }

    public function testShouldCallKlarnaActivate()
    {
        $details = [
            'rno' => 'theRno',
            'osr' => 'theOsr',
            'activation_flags' => 'theFlags',
        ];

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('activate')
            ->with(
                $details['rno'],
                $details['osr'],
                $details['activation_flags']
            )
            ->willReturn(['theRisk', 'theInvNumber'])
        ;

        $action = new ActivateAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($activate = new Activate($details));

        $activatedDetails = $activate->getModel();
        $this->assertSame('theRisk', $activatedDetails['risk_status']);
        $this->assertSame('theInvNumber', $activatedDetails['invoice_number']);
    }

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = [
            'rno' => 'theRno',
            'osr' => 'theOsr',
            'activation_flags' => 'theFlags',
        ];

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('activate')
            ->with(
                $details['rno'],
                $details['osr'],
                $details['activation_flags']
            )
            ->willThrowException(new KlarnaException('theMessage', 123))
        ;

        $action = new ActivateAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($activate = new Activate($details));

        $activatedDetails = $activate->getModel();

        $this->assertSame(123, $activatedDetails['error_code']);
        $this->assertSame('theMessage', $activatedDetails['error_message']);
    }

    protected function getActionClass(): string
    {
        return ActivateAction::class;
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
        $klarnaMock = $this->createMock(Klarna::class, ['config', 'activate', 'cancelReservation', 'checkOrderStatus']);

        $rp = new ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
