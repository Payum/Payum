<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\ActivateAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\Activate;
use PHPUnit\Framework\TestCase;
use PhpXmlRpc\Client;

class ActivateActionTest extends GenericApiAwareActionTest
{
    protected function getActionClass(): string
    {
        return ActivateAction::class;
    }

    protected function getApiClass()
    {
        return new Config();
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\ActivateAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new ActivateAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportActivateWithArrayAsModel()
    {
        $action = new ActivateAction();

        $this->assertTrue($action->supports(new Activate(array())));
    }

    public function testShouldNotSupportAnythingNotActivate()
    {
        $action = new ActivateAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportActivateWithNotArrayAccessModel()
    {
        $action = new ActivateAction();

        $this->assertFalse($action->supports(new Activate(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new ActivateAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfRnoNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The rno fields are required.');
        $action = new ActivateAction();

        $action->execute(new Activate(array()));
    }

    public function testShouldCallKlarnaActivate()
    {
        $details = array(
            'rno' => 'theRno',
            'osr' => 'theOsr',
            'activation_flags' => 'theFlags',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('activate')
            ->with(
                $details['rno'],
                $details['osr'],
                $details['activation_flags']
            )
            ->willReturn(array('theRisk', 'theInvNumber'))
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
        $details = array(
            'rno' => 'theRno',
            'osr' => 'theOsr',
            'activation_flags' => 'theFlags',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('activate')
            ->with(
                $details['rno'],
                $details['osr'],
                $details['activation_flags']
            )
            ->willThrowException(new \KlarnaException('theMessage', 123))
        ;

        $action = new ActivateAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($activate = new Activate($details));

        $activatedDetails = $activate->getModel();

        $this->assertSame(123, $activatedDetails['error_code']);
        $this->assertSame('theMessage', $activatedDetails['error_message']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->createMock('Klarna', array('config', 'activate', 'cancelReservation', 'checkOrderStatus'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
