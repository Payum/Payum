<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\CheckOrderStatusAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CheckOrderStatus;
use PhpXmlRpc\Client;

class CheckOrderStatusActionTest extends GenericApiAwareActionTest
{
    protected function getActionClass(): string
    {
        return CheckOrderStatusAction::class;
    }

    protected function getApiClass(): Config
    {
        return new Config();
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CheckOrderStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new CheckOrderStatusAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportCheckOrderStatusWithArrayAsModel()
    {
        $action = new CheckOrderStatusAction();

        $this->assertTrue($action->supports(new CheckOrderStatus(array())));
    }

    public function testShouldNotSupportAnythingNotCheckOrderStatus()
    {
        $action = new CheckOrderStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportCheckOrderStatusWithNotArrayAccessModel()
    {
        $action = new CheckOrderStatusAction();

        $this->assertFalse($action->supports(new CheckOrderStatus(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CheckOrderStatusAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfRnoNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The rno fields are required.');
        $action = new CheckOrderStatusAction();

        $action->execute(new CheckOrderStatus(array()));
    }

    public function testShouldCallKlarnaCheckOrderStatusMethod()
    {
        $details = array(
            'rno' => 'theRno',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('checkOrderStatus')
            ->with($details['rno'])
            ->willReturn('theStatus')
        ;

        $action = new CheckOrderStatusAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($check = new CheckOrderStatus($details));

        $activatedDetails = $check->getModel();

        $this->assertSame('theStatus', $activatedDetails['status']);
    }

    public function testShouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = array(
            'rno' => 'theRno',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('checkOrderStatus')
            ->with($details['rno'])
            ->willThrowException(new \KlarnaException('theMessage', 123))
        ;

        $action = new CheckOrderStatusAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($activate = new CheckOrderStatus($details));

        $activatedDetails = $activate->getModel();
        $this->assertSame(123, $activatedDetails['error_code']);
        $this->assertSame('theMessage', $activatedDetails['error_message']);
    }

    public function testShouldDoNothingIfAlreadyActivated()
    {
        $details = array(
            'rno' => 'theRno',
            'invoice_number' => 'anInvNumber',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->never())
            ->method('checkOrderStatus')
        ;

        $action = new CheckOrderStatusAction($klarnaMock);

        $action->execute($activate = new CheckOrderStatus($details));
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
