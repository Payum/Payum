<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Klarna\Invoice\Action\Api\CheckOrderStatusAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CheckOrderStatus;
use PHPUnit\Framework\TestCase;
use PhpXmlRpc\Client;

class CheckOrderStatusActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CheckOrderStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CheckOrderStatusAction();
    }

    /**
     * @test
     */
    public function couldBeConstructedWithKlarnaAsArgument()
    {
        new CheckOrderStatusAction($this->createKlarnaMock());
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = new CheckOrderStatusAction($this->createKlarnaMock());

        $action->setApi($config = new Config());

        $this->assertAttributeSame($config, 'config', $action);
    }

    /**
     * @test
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new CheckOrderStatusAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCheckOrderStatusWithArrayAsModel()
    {
        $action = new CheckOrderStatusAction();

        $this->assertTrue($action->supports(new CheckOrderStatus(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCheckOrderStatus()
    {
        $action = new CheckOrderStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCheckOrderStatusWithNotArrayAccessModel()
    {
        $action = new CheckOrderStatusAction();

        $this->assertFalse($action->supports(new CheckOrderStatus(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CheckOrderStatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfRnoNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The rno fields are required.');
        $action = new CheckOrderStatusAction();

        $action->execute(new CheckOrderStatus(array()));
    }

    /**
     * @test
     */
    public function shouldCallKlarnaCheckOrderStatusMethod()
    {
        $details = array(
            'rno' => 'theRno',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('checkOrderStatus')
            ->with($details['rno'])
            ->will($this->returnValue('theStatus'))
        ;

        $action = new CheckOrderStatusAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($check = new CheckOrderStatus($details));

        $activatedDetails = $check->getModel();

        $this->assertEquals('theStatus', $activatedDetails['status']);
    }

    /**
     * @test
     */
    public function shouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = array(
            'rno' => 'theRno',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('checkOrderStatus')
            ->with($details['rno'])
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new CheckOrderStatusAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($activate = new CheckOrderStatus($details));

        $activatedDetails = $activate->getModel();
        $this->assertEquals(123, $activatedDetails['error_code']);
        $this->assertEquals('theMessage', $activatedDetails['error_message']);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfAlreadyActivated()
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
        $rp->setValue($klarnaMock, $this->createMock(Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
