<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\GetAddressesAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\GetAddresses;
use PHPUnit\Framework\TestCase;
use PhpXmlRpc\Client;

class GetAddressesActionTest extends GenericApiAwareActionTest
{
    protected function getActionClass(): string
    {
        return GetAddressesAction::class;
    }

    protected function getApiClass()
    {
        return new Config();
    }

    public function testShouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\GetAddressesAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new GetAddressesAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportGetAddressesRequest()
    {
        $action = new GetAddressesAction();

        $this->assertTrue($action->supports(new GetAddresses('pno')));
    }

    public function testShouldNotSupportAnythingNotGetAddresses()
    {
        $action = new GetAddressesAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new GetAddressesAction();

        $action->execute(new \stdClass());
    }

    public function testShouldCallKlarnaGetAddresses()
    {
        $first = new \KlarnaAddr();
        $first->setCountry('SE');

        $second = new \KlarnaAddr();
        $second->setCountry('SE');

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('getAddresses')
            ->with('thePno')
            ->willReturn(array($first, $second))
        ;

        $action = new GetAddressesAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($getAddresses = new GetAddresses('thePno'));

        $this->assertCount(2, $getAddresses->getAddresses());
        $this->assertSame($first, $getAddresses->getFirstAddress());
    }

    public function testShouldNotCatchKlarnaException()
    {
        $this->expectException(\KlarnaException::class);
        $details = array(
            'pno' => 'thePno',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('getAddresses')
            ->with($details['pno'])
            ->willThrowException(new \KlarnaException('theMessage', 123))
        ;

        $action = new GetAddressesAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($getAddresses = new GetAddresses('thePno'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->createMock('Klarna', array('config', 'getAddresses'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
