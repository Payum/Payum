<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\SkipOnPhp7Trait;
use Payum\Klarna\Invoice\Action\Api\GetAddressesAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\GetAddresses;

class GetAddressesActionTest extends \PHPUnit_Framework_TestCase
{
    use SkipOnPhp7Trait;

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\GetAddressesAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new GetAddressesAction();
    }

    /**
     * @test
     */
    public function couldBeConstructedWithKlarnaAsArgument()
    {
        new GetAddressesAction($this->createKlarnaMock());
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = new GetAddressesAction($this->createKlarnaMock());

        $action->setApi($config = new Config());

        $this->assertAttributeSame($config, 'config', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     * @expectedExceptionMessage Instance of Config is expected to be passed as api.
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $action = new GetAddressesAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportGetAddressesRequest()
    {
        $action = new GetAddressesAction();

        $this->assertTrue($action->supports(new GetAddresses('pno')));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetAddresses()
    {
        $action = new GetAddressesAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new GetAddressesAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallKlarnaGetAddresses()
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
            ->will($this->returnValue(array($first, $second)))
        ;

        $action = new GetAddressesAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($getAddresses = new GetAddresses('thePno'));

        $this->assertCount(2, $getAddresses->getAddresses());
        $this->assertSame($first, $getAddresses->getFirstAddress());
    }

    /**
     * @test
     *
     * @expectedException \KlarnaException
     */
    public function shouldNotCatchKlarnaException()
    {
        $details = array(
            'pno' => 'thePno',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('getAddresses')
            ->with($details['pno'])
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
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
        $klarnaMock =  $this->getMock('Klarna', array('config', 'getAddresses'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->getMock('xmlrpc_client', array(), array(), '', false));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
